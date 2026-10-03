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
        <div class="nav-menu">
            <a href="index.php#daftar-job">Cari Bantuan</a>

            <?php if (isLoggedIn() && currentUserRole() === "requester"): ?>
                <a href="buat.php">Buat Request</a>
                <a href="job-saya.php">Job Saya</a>
            <?php endif; ?>

            <?php if (isLoggedIn() && currentUserRole() === "helper"): ?>
                <a href="lamaran.php">Lamaran Saya</a>
            <?php endif; ?>

            <?php if (isLoggedIn()): ?>
                <a href="riwayat.php">Riwayat</a>
            <?php endif; ?>

            <a href="tentang.php">Tentang</a>

            <?php if (isLoggedIn()): ?>
                <span class="nav-user">Halo, <?php echo e($_SESSION["nama"]); ?></span>
                <a href="logout.php">Keluar</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-nav">Masuk</a>
            <?php endif; ?>
        </div>
    </nav>
</header>