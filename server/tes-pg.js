const { Pool } = require("pg");

const pool = new Pool({
    host: "localhost",
    port: 5433,             // ← port milik PostgreSQL 17 yang kamu catat
    user: "postgres",
    password: "16ibnu19",// ← password yang kamu set saat install
    database: "tolongin",
});

async function main() {
    const hasil = await pool.query(
        "SELECT id, judul, lokasi, imbalan, status FROM jobs ORDER BY id LIMIT 5"
    );
    console.log("Jumlah baris:", hasil.rowCount);
    console.table(hasil.rows);
    await pool.end();
}

main().catch(function (err) {
    console.error("Gagal koneksi:", err.message);
});