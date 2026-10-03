<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/functions.php';

requireLogin();

 $id = (int)($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// syarat kepemilikan ditanam di SQL: job harus ada DAN milik session ini
 $stmt = mysqli_prepare($konek, "SELECT * FROM jobs WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $id, $_SESSION["user_id"]);
mysqli_stmt_execute($stmt);
 $job = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$job) {
    http_response_code(403);
    die("<h1>403 — Akses ditolak</h1><p>Job ini bukan milikmu.</p>");
}

// pelamar job ini — terlama dulu (yang pertama melamar, pertama dipertimbangkan)
 $stmt = mysqli_prepare($konek,
    "SELECT applications.id, applications.status, applications.created_at,
            users.nama
     FROM applications
     INNER JOIN users ON users.id = applications.user_id
     WHERE applications.job_id = ?
     ORDER BY applications.created_at ASC");

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
 $pelamar = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

 $judul_halaman = "Pelamar — " . $job["judul"];
include 'includes/header.php';
?>

<main>
    <section class="page-content">
        <h2>Pelamar</h2>

        <div class="app-row">
            <div class="app-main">
                <a href="job.php?id=<?php echo (int)$job["id"]; ?>"><?php echo e($job["judul"]); ?></a>
                <p class="app-meta">
                    <span class="badge badge-<?php echo strtolower(e($job["status"])); ?>">
                        <?php echo e($job["status"]); ?>
                    </span>
                    · <?php echo formatRupiah($job["imbalan"]); ?>
                </p>
            </div>
        </div>

        <?php if ($job["status"] !== "OPEN"): ?>
            <p class="notice">Helper sudah dipilih untuk job ini. Lamaran lain otomatis ditolak.</p>
        <?php endif; ?>

        <?php if (count($pelamar) === 0): ?>
            <p class="empty-state">Belum ada pelamar. Job ini masih menunggu orang yang tepat.</p>
        <?php else: ?>
            <div class="app-list" style="margin-top: 24px;">
                <?php foreach ($pelamar as $p): ?>
                    <div class="app-row">
                        <div class="app-main">
                            <span><?php echo e($p["nama"]); ?></span>
                            <p class="app-meta">
                                mengajukan diri <?php echo date("d M Y, H:i", strtotime($p["created_at"])); ?>
                            </p>
                        </div>

                        <div class="app-actions">
                            <span class="badge badge-<?php echo strtolower(e($p["status"])); ?>">
                                <?php echo e($p["status"]); ?>
                            </span>

                            <?php if ($p["status"] === "PENDING" && $job["status"] === "OPEN"): ?>
                                <form method="post" action="keputusan.php">
                                    <input type="hidden" name="application_id" value="<?php echo (int)$p["id"]; ?>">
                                    <input type="hidden" name="job_id" value="<?php echo (int)$job["id"]; ?>">
                                    <button type="submit" name="aksi" value="terima" class="btn btn-sm">Terima</button>
                                </form>
                                <form method="post" action="keputusan.php">
                                    <input type="hidden" name="application_id" value="<?php echo (int)$p["id"]; ?>">
                                    <input type="hidden" name="job_id" value="<?php echo (int)$job["id"]; ?>">
                                    <button type="submit" name="aksi" value="tolak" class="btn btn-danger btn-sm">Tolak</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include 'includes/footer.php'; ?>