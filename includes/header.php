<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $judul_halaman ?? "Tolongin!"; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=12">
</head>
<body>

<header>
    <nav>
        <a href="index.php" class="logo">Tolongin<span class="logo-accent">!</span></a>
        <div class="header-right">
             <?php if (isLoggedIn()): ?>
                 <span class="nav-user">Halo, <?php echo e($_SESSION["nama"]); ?></span>
             <?php endif; ?>
        </div>
    </nav>
</header>

<?php $halaman_ini = basename($_SERVER["PHP_SELF"]); ?>
<nav class="bottom-nav">
    <a href="index.php" class="bn-link<?php echo ($halaman_ini === "index.php") ? " active" : ""; ?>">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <span>Cari</span>
    </a>

    <?php if (isLoggedIn() && currentUserRole() === "requester"): ?>
        <a href="buat.php" class="bn-link<?php echo ($halaman_ini === "buat.php") ? " active" : ""; ?>">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
            <span>Buat</span>
        </a>
        <a href="job-saya.php" class="bn-link<?php echo ($halaman_ini === "job-saya.php") ? " active" : ""; ?>">
            <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            <span>Job</span>
        </a>
    <?php endif; ?>

    <?php if (isLoggedIn() && currentUserRole() === "helper"): ?>
        <a href="lamaran.php" class="bn-link<?php echo ($halaman_ini === "lamaran.php") ? " active" : ""; ?>">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            <span>Lamaran</span>
        </a>
    <?php endif; ?>

    <?php if (isLoggedIn()): ?>
        <a href="riwayat.php" class="bn-link<?php echo ($halaman_ini === "riwayat.php") ? " active" : ""; ?>">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span>Riwayat</span>
        </a>
        <a href="logout.php" class="bn-link">
            <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            <span>Keluar</span>
        </a>
    <?php else: ?>
        <a href="login.php" class="bn-link<?php echo ($halaman_ini === "login.php") ? " active" : ""; ?>">
            <svg viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            <span>Masuk</span>
        </a>
    <?php endif; ?>
</nav>