// ============================================================
// server.js — M22-lanjutan: Express + PostgreSQL
// Kontrak JSON identik M16: { success, data|message, meta }
// Sumber data: PostgreSQL (gudang M21) — bukan lagi array dummy
// ============================================================

const express = require("express");
const cors    = require("cors");
const crypto  = require("crypto");
const bcrypt  = require("bcryptjs");
const pool    = require("./db-pg");

const app  = express();
const PORT = 3000;

// ===== middleware =====
app.use(cors());

app.use(function (req, res, next) {
    console.log(`${req.method} ${req.url}`);
    next();
});

app.use(express.json());

// ===== helper: identitas dari token (dicek di DATABASE) =====
// async karena melakukan query — SEMUA pemanggil wajib `await apiUser(req)`
async function apiUser(req) {
    const header = req.headers["authorization"] ?? "";
    const match = /^Bearer\s+(.+)$/i.exec(header);
    if (!match) return null;

    const token = match[1].trim();

    const hasil = await pool.query(
        "SELECT id, nama, email, role FROM users WHERE api_token = $1",
        [token]
    );
    return hasil.rows[0] ?? null;
}

// ============================================================
// POST /api/login — verifikasi password (bcrypt) → token
// ============================================================
app.post("/api/login", async function (req, res) {
    const email    = String(req.body.email ?? "").trim();
    const password = String(req.body.password ?? "");

    if (email === "" || password === "") {
        return res.status(422).json({
            success: false,
            message: "Email dan password wajib diisi.",
        });
    }

    try {
        // Langkah 1 (M8): cari user berdasarkan EMAIL saja
        const hasil = await pool.query(
            "SELECT id, nama, email, password, role FROM users WHERE email = $1",
            [email]
        );
        const user = hasil.rows[0];

        // Langkah 2 (M8): verifikasi password di server — pesan generik.
        // bcryptjs memahami hash $2y$... buatan PHP ✓ (hash dibawa dari MySQL)
        const cocok = user
            ? await bcrypt.compare(password, user.password)
            : false;

        if (!cocok) {
            return res.status(401).json({
                success: false,
                message: "Email atau password salah.",
            });
        }

        // token acak 64 karakter → disimpan ke database (kolom api_token)
        const token = crypto.randomBytes(32).toString("hex");
        await pool.query(
            "UPDATE users SET api_token = $1 WHERE id = $2",
            [token, user.id]
        );

        res.status(200).json({
            success: true,
            message: "Login berhasil.",
            data: {
                token: token,
                user: {
                    id: parseInt(user.id, 10),
                    nama: user.nama,
                    email: user.email,
                    role: user.role,
                },
            },
        });
    } catch (err) {
        console.error(err);
        res.status(500).json({ success: false, message: "Gagal memproses login." });
    }
});

// ============================================================
// GET /api/jobs — daftar job OPEN + pencarian (publik)
// ============================================================
app.get("/api/jobs", async function (req, res) {
    try {
        const q = String(req.query.q ?? "").trim();

        // $1 = status; $2 = pola pencarian (ILIKE = LIKE tanpa peduli huruf besar-kecil)
        // trik "$2 = '' OR ..." → kalau pencarian kosong, kondisi kedua mengalah
        const sql = `
            SELECT id, judul, lokasi, deskripsi, tanggal, imbalan, status, created_at
            FROM jobs
            WHERE status = $1
              AND ($2 = '' OR judul ILIKE $2 OR lokasi ILIKE $2 OR deskripsi ILIKE $2)
            ORDER BY created_at DESC
        `;

        const pattern = q === "" ? "" : `%${q}%`;
        const hasil = await pool.query(sql, ["OPEN", pattern]);

        // pagination (konsep M14.5 — dihitung dari total hasil query)
        const limit = 4;
        const total = hasil.rowCount;
        const totalPages = Math.max(1, Math.ceil(total / limit));
        const page = Math.max(1, parseInt(req.query.page ?? "1", 10) || 1);
        const safePage = Math.min(page, totalPages);
        const data = hasil.rows.slice((safePage - 1) * limit, safePage * limit);

        // DECIMAL dari pg datang sebagai string "50000.00" → kirim sebagai ANGKA
        // (kontrak M16: imbalan adalah number)
        const bersih = data.map(function (j) {
            return {
                ...j,
                id: parseInt(j.id, 10),
                imbalan: parseFloat(j.imbalan),
            };
        });

        res.status(200).json({
            success: true,
            data: bersih,
            meta: {
                total: total,
                page: safePage,
                total_pages: totalPages,
                per_page: limit,
            },
        });
    } catch (err) {
        console.error(err);
        res.status(500).json({ success: false, message: "Gagal membaca database." });
    }
});

// ============================================================
// GET /api/jobs/:id — detail satu job (publik)
// ============================================================
app.get("/api/jobs/:id", async function (req, res) {
    const id = parseInt(req.params.id, 10);

    if (isNaN(id) || id <= 0) {
        return res.status(422).json({
            success: false,
            message: "Parameter id wajib angka yang valid.",
        });
    }

    try {
        const hasil = await pool.query(
            `SELECT jobs.id, jobs.judul, jobs.lokasi, jobs.deskripsi,
                    jobs.tanggal, jobs.imbalan, jobs.status, jobs.created_at,
                    users.nama AS pemilik
             FROM jobs
             LEFT JOIN users ON users.id = jobs.user_id
             WHERE jobs.id = $1`,
            [id]
        );

        const job = hasil.rows[0];
        if (!job) {
            return res.status(404).json({
                success: false,
                message: `Job dengan id ${id} tidak ditemukan.`,
            });
        }

        res.status(200).json({
            success: true,
            data: {
                ...job,
                id: parseInt(job.id, 10),
                imbalan: parseFloat(job.imbalan),
            },
        });
    } catch (err) {
        console.error(err);
        res.status(500).json({ success: false, message: "Gagal membaca database." });
    }
});

// ============================================================
// POST /api/jobs — buat job (token + requester)
// Urutan satpam: 401 authentication → 403 authorization → 422 validasi
// ============================================================
app.post("/api/jobs", async function (req, res) {
    // 1. authentication
    const user = await apiUser(req);
    if (!user) {
        return res.status(401).json({
            success: false,
            message: "Tidak terautentikasi. Kirim header: Authorization: Bearer <token>",
        });
    }

    // 2. authorization
    if (user.role !== "requester") {
        return res.status(403).json({
            success: false,
            message: "Hanya requester yang bisa membuat job.",
        });
    }

    // 3. validasi (logika validasiJob M6, versi JS)
    const judul     = String(req.body.judul ?? "").trim();
    const lokasi    = String(req.body.lokasi ?? "").trim();
    const deskripsi = String(req.body.deskripsi ?? "").trim();
    const tanggal   = String(req.body.tanggal ?? "").trim();
    const imbalan   = Number(req.body.imbalan);

    const errors = [];
    if (judul === "")   errors.push("Judul wajib diisi.");
    if (lokasi === "")  errors.push("Lokasi wajib diisi.");
    if (tanggal === "") errors.push("Tanggal & jam wajib diisi.");
    if (req.body.imbalan === undefined || isNaN(imbalan) || imbalan < 0) {
        errors.push("Imbalan harus berupa angka yang tidak negatif.");
    }
    if (errors.length > 0) {
        return res.status(422).json({
            success: false,
            message: errors.join(" "),
        });
    }

    // 4. aksi — INSERT (identitas dari TOKEN, bukan body — M10)
    try {
        // format browser "2026-09-30T14:00" → format PG "2026-09-30 14:00:00"
        const tanggalSql = tanggal.replace("T", " ") + (tanggal.length === 16 ? ":00" : "");

        const hasil = await pool.query(
            `INSERT INTO jobs (judul, lokasi, deskripsi, tanggal, imbalan, user_id)
             VALUES ($1, $2, $3, $4, $5, $6)
             RETURNING id`,
            [judul, lokasi, deskripsi, tanggalSql, imbalan, user.id]
        );

        res.status(201).json({
            success: true,
            message: "Job berhasil dibuat.",
            data: { id: parseInt(hasil.rows[0].id, 10) },
        });
    } catch (err) {
        console.error(err);
        res.status(500).json({ success: false, message: "Gagal menyimpan job." });
    }
});

// ===== POS TERAKHIR: tak ada rute yang cocok → 404 =====
app.use(function (req, res) {
    res.status(404).json({
        success: false,
        message: `Endpoint ${req.method} ${req.url} tidak ditemukan.`,
    });
});

app.listen(PORT, function () {
    console.log(`API Tolongin! (Node + Express + PostgreSQL) di http://localhost:${PORT}`);
    console.log("Kontrak JSON identik M16 — React (5173) siap mengonsumsi.");
});