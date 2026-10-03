<?php
include 'includes/auth.php';
include 'includes/functions.php';

 $judul_halaman = "Tentang — Tolongin!";
include 'includes/header.php';
?>

<main>
    <section class="hero">
        <h1>Tentang Tolongin!</h1>
        <p>Yang butuh bantuan bertemu yang siap tolong — dimulai dari kota Padang.</p>
    </section>

    <section class="page-content">

        <h2>Kisah di baliknya</h2>
        <p>Ide ini lahir dari situasi yang familiar: sebuah kulkas harus turun dari lantai
            dua, satu orang tidak kuat, tetangga sedang tidak ada. Pekerjaannya sebenarnya
            kecil — hanya butuh satu tangan tambahan selama lima belas menit, dan imbalan
            secukupnya sebagai tanda terima kasih.</p>
        <p>Tolongin! ada untuk momen seperti itu: pekerjaan kecil yang nyata, orang yang
            tidak selalu tersedia di sekitar, dan keyakinan bahwa selalu ada saja orang
            yang siap tolong.</p>

        <h2>Cara kerjanya</h2>
        <ol>
            <li>Yang butuh bantuan memasang job — dijelaskan apa, di mana, kapan, dan imbalannya.</li>
            <li>Yang siap tolong mengajukan diri; pemilik job memilih yang paling pas.</li>
            <li>Pekerjaan berjalan sampai selesai, lalu keduanya saling memberi ulasan.</li>
        </ol>

        <h2>Dua peran, satu tujuan</h2>
        <p><strong>Requester</strong> — kamu yang sedang butuh bantuan: memasang job,
            memilih helper, menandai pekerjaan selesai, dan memberi ulasan.</p>
        <p><strong>Helper</strong> — kamu yang siap membantu: mencari job yang cocok,
            mengajukan diri, melaksanakannya, dan mendapatkan imbalan yang disepakati.</p>

        <h2>Yang sudah bisa dipakai</h2>
        <div class="app-list">
            <div class="app-row">
                <div class="app-main">
                    <span>Pasang &amp; kelola job</span>
                    <p class="app-meta">Buat, sunting, batalkan — dengan status yang jelas di setiap tahap</p>
                </div>
                <span class="badge badge-open">Aktif</span>
            </div>
            <div class="app-row">
                <div class="app-main">
                    <span>Ajukan diri &amp; keputusan</span>
                    <p class="app-meta">Helper melamar, requester menerima atau menolak dengan satu klik</p>
                </div>
                <span class="badge badge-open">Aktif</span>
            </div>
            <div class="app-row">
                <div class="app-main">
                    <span>Siklus pekerjaan</span>
                    <p class="app-meta">Dari terpilih sampai selesai — semua tercatat</p>
                </div>
                <span class="badge badge-open">Aktif</span>
            </div>
            <div class="app-row">
                <div class="app-main">
                    <span>Ulasan bintang</span>
                    <p class="app-meta">Kedua pihak saling menilai setelah pekerjaan selesai</p>
                </div>
                <span class="badge badge-open">Aktif</span>
            </div>
            <div class="app-row">
                <div class="app-main">
                    <span>Pencarian &amp; riwayat</span>
                    <p class="app-meta">Cari job lewat kata kunci; pekerjaan lampau tersimpan rapi</p>
                </div>
                <span class="badge badge-open">Aktif</span>
            </div>
        </div>

        <h2>Dalam rencana</h2>
        <p class="app-meta" style="margin-bottom: 12px;">
            Fitur berikut diperkirakan datang setelah inti aplikasi stabil:</p>
        <div class="app-list">
            <div class="app-row">
                <div class="app-main">
                    <span>Pembayaran terjamin</span>
                    <p class="app-meta">Imbalan ditahan sistem, cair saat pekerjaan disetujui</p>
                </div>
                <span class="badge badge-pending">Segera</span>
            </div>
            <div class="app-row">
                <div class="app-main">
                    <span>Chat antar pengguna</span>
                    <p class="app-meta">Koordinasi detail tanpa keluar dari aplikasi</p>
                </div>
                <span class="badge badge-pending">Segera</span>
            </div>
            <div class="app-row">
                <div class="app-main">
                    <span>Notifikasi</span>
                    <p class="app-meta">Tahu lamaranmu diterima tanpa membuka halaman terus-menerus</p>
                </div>
                <span class="badge badge-pending">Segera</span>
            </div>
        </div>

        <h2>Sebuah proyek belajar</h2>
        <p>Tolongin! dibangun dari nol sebagai proyek belajar web development:
            PHP untuk logika di sisi server, MySQL untuk menyimpan data, serta HTML
            dan CSS untuk tampilannya — sengaja tanpa framework, agar setiap bagian
            dipahami cara kerjanya, bukan sekadar dipakai.</p>
        <p class="app-meta">Dibuat di Padang, untuk warga Padang.</p>

    </section>
</main>

<?php include 'includes/footer.php'; ?>