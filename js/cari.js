"use strict";
// ============================================================
// cari.ts — versi TypeScript (M18)
// Sumber: ts/cari.ts → tsc (via npm run watch/build) → js/cari.js
// ============================================================
// ===== 2. Menggenggam elemen DOM =====
// getElementById hanya menjanjikan HTMLElement | null.
// `as` = type assertion: "aku yang menjamin tipe detailnya".
// `!`  = "aku yang menjamin elemen ini pasti ada".
const inputCari = document.getElementById("input-cari");
const formCari = document.getElementById("form-cari");
const daftarJob = document.getElementById("daftar-job");
const infoHasil = document.getElementById("info-hasil");
const statusArea = document.getElementById("status-area");
const API = "api/jobs.php";
let timer = undefined;
// ===== 3. Helper format =====
function formatTanggal(iso) {
    const d = new Date(iso.replace(" ", "T"));
    if (isNaN(d.getTime())) {
        return iso;
    }
    return d.toLocaleDateString("id-ID", {
        day: "2-digit", month: "short", year: "numeric",
    }) + ", " + d.toLocaleTimeString("id-ID", {
        hour: "2-digit", minute: "2-digit",
    });
}
function formatRupiah(angka) {
    return "Rp" + angka.toLocaleString("id-ID");
}
// ===== 4. Bangun satu kartu (aman dari XSS — tetap textContent) =====
function buatKartu(job) {
    const card = document.createElement("article");
    card.className = "job-card";
    const h3 = document.createElement("h3");
    h3.textContent = job.judul;
    const ul = document.createElement("ul");
    const liLokasi = document.createElement("li");
    liLokasi.textContent = job.lokasi;
    const liWaktu = document.createElement("li");
    liWaktu.textContent = formatTanggal(job.tanggal);
    const liImbalan = document.createElement("li");
    liImbalan.textContent = "Imbalan: ";
    const reward = document.createElement("span");
    reward.className = "reward";
    reward.textContent = formatRupiah(job.imbalan);
    liImbalan.appendChild(reward);
    ul.append(liLokasi, liWaktu, liImbalan);
    const link = document.createElement("a");
    link.href = "job.php?id=" + encodeURIComponent(job.id);
    link.textContent = "Lihat detail";
    card.append(h3, ul, link);
    return card;
}
// ===== 5. Tampilkan hasil =====
function tampilkan(jobs, meta) {
    statusArea.textContent = "";
    if (jobs.length === 0) {
        infoHasil.textContent = "";
        statusArea.textContent = "Tidak ada job yang cocok dengan pencarianmu.";
        return;
    }
    infoHasil.textContent =
        `${meta.total} job ditemukan · halaman ${meta.page} dari ${meta.total_pages}`;
    for (const job of jobs) {
        daftarJob.appendChild(buatKartu(job));
    }
}
// ===== 6. Inti: fetch + narrowing =====
async function muatJob(kataKunci) {
    daftarJob.innerHTML = "";
    infoHasil.textContent = "";
    statusArea.textContent = "Memuat...";
    try {
        const url = API + "?q=" + encodeURIComponent(kataKunci);
        const res = await fetch(url);
        const json = (await res.json());
        // narrowing: di cabang ini TS TAHU json adalah ApiErr
        if (!res.ok || json.success === false) {
            const pesan = json.success === false
                ? json.message
                : "Respons tidak berhasil.";
            throw new Error(pesan);
        }
        // setelah lolos pemeriksaan — TS TAHU json adalah ApiOk
        tampilkan(json.data, json.meta);
    }
    catch (err) {
        // strict mode: err bertipe unknown — buktikan dulu dia Error
        const pesan = err instanceof Error ? err.message : "Kesalahan tak dikenal.";
        statusArea.textContent = "Terjadi masalah: " + pesan;
        console.error("Detail error:", err);
    }
}
// ===== 7. Event =====
formCari.addEventListener("submit", function (event) {
    event.preventDefault();
    muatJob(inputCari.value.trim());
});
inputCari.addEventListener("input", function () {
    clearTimeout(timer); // aman walau timer undefined
    timer = window.setTimeout(function () {
        muatJob(inputCari.value.trim());
    }, 300);
});
// muat pertama: semua job begitu halaman terbuka
muatJob("");
