<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/functions.php';

requireRole("helper");

 $judul_halaman = "Lamaran Saya — Tolongin!";

 $user_id = (int)$_SESSION["user_id"];

// hanya lamaran pada job AKTIF — yang selesai/dibatalkan pindah ke riwayat.php
 $stmt = mysqli_prepare($konek,
    "SELECT applications.id, applications.status AS status_lamaran,
            applications.created_at,
            jobs.id AS job_id, jobs.judul, jobs.lokasi, jobs.imbalan,
            jobs.tanggal, jobs.status AS status_job
     FROM applications
     INNER JOIN jobs ON jobs.id = applications.job_id
     WHERE applications.user_id = ?
       AND jobs.status IN ('OPEN', 'ACCEPTED', 'IN_PROGRESS')
     ORDER BY applications.created_at DESC");

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
 $lamaran_list = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

include 'includes/header.php';
?>

<main>
    <section class="page-content">
        <h2>Lamaran Saya</h2>

        <?php if (count($lamaran_list) === 0): ?>
            <p class="empty-state">
                Tidak ada lamaran aktif. Buka <a href="index.php">daftar job</a>
                untuk mengajukan diri, atau lihat
                <a href="riwayat.php">riwayat pekerjaan</a>.
            </p>
        <?php else: ?>
            <div class="app-list">
                <?php foreach ($lamaran_list as $l): ?>
                    <div class="app-row">
                        <div class="app-main">
                            <a href="job.php?id=<?php echo (int)$l["job_id"]; ?>">
                                <?php echo e($l["judul"]); ?>
                            </a>
                            <p class="app-meta">
                                <?php echo e($l["lokasi"]); ?> ·
                                <?php echo formatRupiah($l["imbalan"]); ?> ·
                                <?php echo date("d M Y", strtotime($l["tanggal"])); ?>
                            </p>
                            <p class="app-meta">
                                Lamaran:
                                <span class="badge badge-<?php echo strtolower(e($l["status_lamaran"])); ?>">
                                    <?php echo e($l["status_lamaran"]); ?>
                                </span>
                                · Pekerjaan:
                                <span class="badge badge-<?php echo strtolower(e($l["status_job"])); ?>">
                                    <?php echo e($l["status_job"]); ?>
                                </span>
                            </p>
                        </div>

                        <div class="app-actions">
                            <?php if ($l["status_lamaran"] === "PENDING"): ?>
                                <span class="count-chip">Menunggu keputusan</span>

                            <?php elseif ($l["status_lamaran"] === "REJECTED"): ?>
                                <span class="count-chip">Tidak diterima</span>

                            <?php elseif ($l["status_job"] === "ACCEPTED"): ?>
                                <span class="count-chip">Menunggu requester memulai</span>

                            <?php elseif ($l["status_job"] === "IN_PROGRESS"): ?>
                                <span class="count-chip">Pekerjaan berjalan</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include 'includes/footer.php'; ?>