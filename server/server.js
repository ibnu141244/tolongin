// ============================================================
// server.js — M22: REST API Node+Express (data dummy)
// Kontrak identik M16: { success, data|message, meta }
// ============================================================

const cors = require("cors");
const express = require("express");
const crypto  = require("crypto");                 // bawaan Node — token acak
const { users, jobs, nextJobId, setNextJobId } = require("./db");

const app  = express();
const PORT = 3000;
app.use(cors());

// ===== middleware =====
app.use(function (req, res, next) {
    console.log(`${req.method} ${req.url}`);
    next();
});

app.use(express.json()); // req.body tersedia untuk semua rute

// ===== helper: identitas dari token (padanan api_user() M16) =====
function apiUser(req) {
    const header = req.headers["authorization"] ?? "";

    const match = /^Bearer\s+(.+)$/i.exec(header);
    if (!match) return null;

    const token = match[1].trim();
    return users.find(function (u) { return u.api_token === token; }) ?? null;
}

// ===== POST /api/login — dapatkan token =====
app.post("/api/login", function (req, res) {
    const email    = String(req.body.email ?? "").trim();
    const password = String(req.body.password ?? "");

    if (email === "" || password === "") {
        return res.status(422).json({
            success: false,
            message: "Email dan password wajib diisi.",
        });
    }

    const user = users.find(function (u) { return u.email === email; });

    // pesan generik — pelajaran M8
    if (!user || user.password !== password) {
        return res.status(401).json({
            success: false,
            message: "Email atau password salah.",
        });
    }

    // token acak 64 karakter hex (crypto bawaan Node)
    user.api_token = crypto.randomBytes(32).toString("hex");

    res.status(200).json({
        success: true,
        message: "Login berhasil.",
        data: {
            token: user.api_token,
            user: { id: user.id, nama: user.nama, email: user.email, role: user.role },
        },
    });
});

// ===== GET /api/jobs — daftar (publik, hanya OPEN) + pencarian =====
app.get("/api/jobs", function (req, res) {
    const q = String(req.query.q ?? "").trim().toLowerCase();
    const page  = Math.max(1, parseInt(req.query.page ?? "1", 10) || 1);
    const limit = 6;

    // hanya OPEN (aturan marketplace M11) + filter pencarian (judul/lokasi/deskripsi)
    const cocok = jobs.filter(function (j) {
        if (j.status !== "OPEN") return false;
        if (q === "") return true;
        return (
            j.judul.toLowerCase().includes(q) ||
            j.lokasi.toLowerCase().includes(q) ||
            (j.deskripsi ?? "").toLowerCase().includes(q)
        );
    });

    const total = cocok.length;
    const totalPages = Math.max(1, Math.ceil(total / limit));
    const safePage   = Math.min(page, totalPages);
    const offset     = (safePage - 1) * limit;

    // data bersih: password dsb tidak pernah bocor (prinsip M16)
    const data = cocok
        .slice(offset, offset + limit)
        .map(function (j) {
            return {
                id: j.id, judul: j.judul, lokasi: j.lokasi,
                deskripsi: j.deskripsi, tanggal: j.tanggal,
                imbalan: j.imbalan, status: j.status, created_at: j.created_at,
            };
        });

    res.status(200).json({
        success: true,
        data: data,
        meta: { total: total, page: safePage, total_pages: totalPages, per_page: limit },
    });
});

// ===== GET /api/jobs/:id — detail (publik) =====
app.get("/api/jobs/:id", function (req, res) {
    const id = parseInt(req.params.id, 10);

    if (isNaN(id) || id <= 0) {
        return res.status(422).json({
            success: false,
            message: "Parameter id wajib angka yang valid.",
        });
    }

    const job = jobs.find(function (j) { return j.id === id; });
    if (!job) {
        return res.status(404).json({
            success: false,
            message: `Job dengan id ${id} tidak ditemukan.`,
        });
    }

    res.status(200).json({
        success: true,
        data: {
            id: job.id, judul: job.judul, lokasi: job.lokasi,
            deskripsi: job.deskripsi, tanggal: job.tanggal,
            imbalan: job.imbalan, status: job.status,
            created_at: job.created_at,
        },
    });
});

// ===== POST /api/jobs — buat job (token + requester) =====
app.post("/api/jobs", function (req, res) {
    // 1. authentication (401)
    const user = apiUser(req);
    if (!user) {
        return res.status(401).json({
            success: false,
            message: "Tidak terautentikasi. Kirim header: Authorization: Bearer <token>",
        });
    }

    // 2. authorization (403)
    if (user.role !== "requester") {
        return res.status(403).json({
            success: false,
            message: "Hanya requester yang bisa membuat job.",
        });
    }

    // 3. validasi (422) — logika validasiJob M6 dalam bentuk JS
    const judul    = String(req.body.judul ?? "").trim();
    const lokasi   = String(req.body.lokasi ?? "").trim();
    const deskripsi= String(req.body.deskripsi ?? "").trim();
    const tanggal  = String(req.body.tanggal ?? "").trim();
    const imbalan  = Number(req.body.imbalan);

    const errors = [];
    if (judul === "")     errors.push("Judul wajib diisi.");
    if (lokasi === "")    errors.push("Lokasi wajib diisi.");
    if (tanggal === "")   errors.push("Tanggal & jam wajib diisi.");
    if (req.body.imbalan === undefined || isNaN(imbalan) || imbalan < 0) {
        errors.push("Imbalan harus berupa angka yang tidak negatif.");
    }

    if (errors.length > 0) {
        return res.status(422).json({ success: false, message: errors.join(" ") });
    }

    // 4. aksi
    const newJob = {
        id: nextJobId(),
        judul: judul,
        lokasi: lokasi,
        deskripsi: deskripsi,
        tanggal: tanggal.replace("T", " ") + (tanggal.length === 16 ? ":00" : ""),
        imbalan: imbalan,
        status: "OPEN",
        user_id: user.id,                       // identitas dari TOKEN — bukan body (M10)
        created_at: new Date().toISOString().replace("T", " ").slice(0, 19),
    };
    jobs.push(newJob);
    setNextJobId(newJob.id + 1);

    res.status(201).json({
        success: true,
        message: "Job berhasil dibuat.",
        data: { id: newJob.id },
    });
});

// ===== 404 terakhir =====
app.use(function (req, res) {
    res.status(404).json({
        success: false,
        message: `Endpoint ${req.method} ${req.url} tidak ditemukan.`,
    });
});

app.listen(PORT, function () {
    console.log(`API Tolongin! (Node+Express) di http://localhost:${PORT}`);
    console.log("Kontrak JSON identik M16 — cari.ts siap menunjuk ke sini.");
});