<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/functions.php';

// DUA role boleh masuk — isinya menyesuaikan siapa yang membuka
requireLogin();

 $judul_halaman = "Riwayat — Tolongin!";

 $user_id = (int)$_SESSION["user_id"];
 $role    = currentUserRole();

 $riwayat = [];

if ($role === "requester") {

    // job milikku yang sudah terminal (COMPLETED / CANCELLED)
    $stmt = mysqli_prepare($konek,
        "SELECT id AS job_id, judul, lokasi, imbalan, tanggal, status AS status_job
         FROM jobs
         WHERE user_id = ? AND status IN ('COMPLETED', 'CANCELLED')
         ORDER BY tanggal DESC");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $riwayat = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    foreach ($riwayat as $k => $r) {
        $riwayat[$k]["peran"] = "Pemilik";
    }

} else { // helper

    // job yang pernah saya ikuti (diterima) dan sudah terminal.
    // lamaran REJECTED tidak ikut — yang ditolak bukan peserta.
    $stmt = mysqli_prepare($konek,
        "SELECT jobs.id AS job_id, jobs.judul, jobs.lokasi, jobs.imbalan,
                jobs.tanggal, jobs.status AS status_job,
                applications.status AS status_lamaran
         FROM applications
         INNER JOIN jobs ON jobs.id = applications.job_id
         WHERE applications.user_id = ?
           AND applications.status IN ('ACCEPTED', 'CANCELLED')
           AND jobs.status IN ('COMPLETED', 'CANCELLED')
         ORDER BY jobs.tanggal DESC");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $riwayat = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    foreach ($riwayat as $k => $r) {
        $riwayat[$k]["peran"] = "Helper";
    }
}

// ulasan yang sudah saya kirim untuk job-job ini (pola IN dinamis — sama seperti lamaran.php kemarin)
 $jobIds = array_map("intval", array_column($riwayat, "job_id"));
 $myReviews = [];
if (count($jobIds) > 0) {
    $placeholders = implode(",", array_fill(0, count($jobIds), "?"));

    $stmt = mysqli_prepare($konek,
        "SELECT job_id FROM reviews
         WHERE reviewer_id = ? AND job_id IN ($placeholders)");
    mysqli_stmt_bind_param($stmt, "i" . str_repeat("i", count($jobIds)),
        $user_id, ...$jobIds);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($hasil)) {
        $myReviews[(int)$r["job_id"]] = true;
    }
    mysqli_stmt_close($stmt);
}

foreach ($riwayat as $k => $r) {
    $riwayat[$k]["sudah_review"] = isset($myReviews[(int)$r["job_id"]]);
}

include 'includes/header.php';
?>

<main>
    <section class="page-content">
        <h2>Riwayat</h2>

        <?php if (count($riwayat) === 0): ?>
            <p class="empty-state">
                Belum ada riwayat. Job yang selesai atau dibatalkan
                akan tersimpan di sini.
            </p>
        <?php else: ?>
            <div class="app-list">
                <?php foreach ($riwayat as $r): ?>
                    <div class="app-row">
                        <div class="app-main">
                            <a href="job.php?id=<?php echo (int)$r["job_id"]; ?>">
                                <?php echo e($r["judul"]); ?>
                            </a>
                            <p class="app-meta">
                                Sebagai <?php echo e($r["peran"]); ?> ·
                                <?php echo e($r["lokasi"]); ?> ·
                                <?php echo formatRupiah($r["imbalan"]); ?> ·
                                <?php echo date("d M Y", strtotime($r["tanggal"])); ?>
                            </p>
                            <p class="app-meta">
                                Status:
                                <span class="badge badge-<?php echo strtolower(e($r["status_job"])); ?>">
                                    <?php echo e($r["status_job"]); ?>
                                </span>
                            </p>
                        </div>

                        <div class="app-actions">
                            <?php if ($r["status_job"] === "COMPLETED" && $r["sudah_review"]): ?>
                                <a href="job.php?id=<?php echo (int)$r["job_id"]; ?>" class="btn btn-sm">Lihat Ulasan</a>
                            <?php elseif ($r["status_job"] === "COMPLETED"): ?>
                                <a href="review.php?id=<?php echo (int)$r["job_id"]; ?>" class="btn btn-sm">Beri Ulasan</a>
                            <?php else: ?>
                                <a href="job.php?id=<?php echo (int)$r["job_id"]; ?>" class="btn btn-sm">Lihat Detail</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include 'includes/footer.php'; ?>