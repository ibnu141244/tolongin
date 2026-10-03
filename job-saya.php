<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/functions.php';

requireRole("requester");

 $judul_halaman = "Job Saya — Tolongin!";

 $user_id = (int)$_SESSION["user_id"];

// hanya job AKTIF — yang selesai/dibatalkan pindah ke riwayat.php
 $stmt = mysqli_prepare($konek,
    "SELECT jobs.id, jobs.judul, jobs.lokasi, jobs.status,
            jobs.imbalan, jobs.tanggal,
            COUNT(applications.id) AS jumlah_pelamar
     FROM jobs
     LEFT JOIN applications ON applications.job_id = jobs.id
     WHERE jobs.user_id = ?
       AND jobs.status IN ('OPEN', 'ACCEPTED', 'IN_PROGRESS')
     GROUP BY jobs.id
     ORDER BY jobs.created_at DESC");

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
 $job_list = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

include 'includes/header.php';
?>

<main>
    <section class="page-content">
        <h2>Job Saya</h2>

        <?php if (count($job_list) === 0): ?>
            <p class="empty-state">
                Tidak ada job aktif.
                <a href="buat.php">Buat request baru</a>, atau lihat
                <a href="riwayat.php">riwayat pekerjaan selesai</a>.
            </p>
        <?php else: ?>
            <div class="app-list">
                <?php foreach ($job_list as $j): ?>
                    <div class="app-row">
                        <div class="app-main">
                            <a href="job.php?id=<?php echo (int)$j["id"]; ?>">
                                <?php echo e($j["judul"]); ?>
                            </a>
                            <p class="app-meta">
                                <span class="badge badge-<?php echo strtolower(e($j["status"])); ?>">
                                    <?php echo e($j["status"]); ?>
                                </span>
                                · <?php echo e($j["lokasi"]); ?> ·
                                <?php echo formatRupiah($j["imbalan"]); ?> ·
                                <?php echo date("d M Y", strtotime($j["tanggal"])); ?>
                            </p>
                        </div>

                        <div class="app-actions">
                            <?php if ((int)$j["jumlah_pelamar"] > 0): ?>
                                <span class="count-chip"><?php echo (int)$j["jumlah_pelamar"]; ?> pelamar</span>
                            <?php endif; ?>

                            <?php if ($j["status"] === "OPEN"): ?>
                                <a href="pelamar.php?id=<?php echo (int)$j["id"]; ?>" class="btn btn-sm">Kelola Pelamar</a>

                            <?php elseif ($j["status"] === "ACCEPTED"): ?>
                                <form method="post" action="status.php">
                                    <input type="hidden" name="job_id" value="<?php echo (int)$j["id"]; ?>">
                                    <input type="hidden" name="status_baru" value="IN_PROGRESS">
                                    <button type="submit" class="btn btn-sm">Mulai Kerja</button>
                                </form>

                            <?php elseif ($j["status"] === "IN_PROGRESS"): ?>
                                <form method="post" action="status.php">
                                    <input type="hidden" name="job_id" value="<?php echo (int)$j["id"]; ?>">
                                    <input type="hidden" name="status_baru" value="COMPLETED">
                                    <button type="submit" class="btn btn-sm">Tandai Selesai</button>
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