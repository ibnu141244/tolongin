// ============================================================
// db-pg.js — Pool koneksi PostgreSQL (M22-lanjutan)
// Pool = kolam koneksi siap pakai: dipinjam per query,
// dikembalikan otomatis. Jangan pool.end() di sini — server hidup terus!
// ============================================================

const { Pool } = require("pg");

const pool = new Pool({
    host: "localhost",
    port: 5433,               // ← port PostgreSQL 17-mu (cek Properties server bila perlu)
    user: "postgres",
    password: "16ibnu19",  // ← GANTI: password dari instalasimu
    database: "tolongin",
});

module.exports = pool;