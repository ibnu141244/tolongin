<?php
// Halaman live-search: kerangka di-render server, ISI di-render browser.
// Dependency yang dibutuhkan HANYA auth (untuk nav) + functions (untuk e() di header).
// Tidak ada db.php — halaman ini tidak melakukan satu query pun!
include 'includes/auth.php';
include 'includes/functions.php';

 $judul_halaman = "Cari Job (Live) — Tolongin!";
include 'includes/header.php';
?>

<main>
    <section class="page-content">
        <h2>Cari Job — Live</h2>
        <p class="app-meta">
            Hasil diambil langsung dari API — tanpa halaman dimuat ulang.
        </p>

        <form class="search-panel" id="form-cari" autocomplete="off">
            <input type="text" id="input-cari" placeholder="Ketik untuk mencari...">
            <button type="submit" class="btn">Cari</button>
        </form>

        <p class="result-count" id="info-hasil"></p>
        <p id="status-area" class="app-meta"></p>
        <div class="job-list" id="daftar-job"></div>
    </section>
</main>

<script src="js/cari.js"></script>

<?php include 'includes/footer.php'; ?>