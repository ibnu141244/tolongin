<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/functions.php';

 $id = (int)($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// job + nama pemilik (LEFT JOIN — job tanpa pemilik tetap tampil)
 $stmt = mysqli_prepare($konek,
    "SELECT jobs.*, users.nama AS nama_pemilik
     FROM jobs
     LEFT JOIN users ON users.id = jobs.user_id
     WHERE jobs.id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
 $hasil = mysqli_stmt_get_result($stmt);
 $job   = mysqli_fetch_assoc($hasil);
mysqli_stmt_close($stmt);

if (!$job) {
    header("Location: index.php");
    exit;
}

 $isPemilik = isLoggedIn() && (int)$job["user_id"] === (int)$_SESSION["user_id"];
 $isHelper  = isLoggedIn() && currentUserRole() === "helper";

// lamaran helper yang sedang login di job ini
 $lamaran = null;
if ($isHelper) {
    $user_id = (int)$_SESSION["user_id"];

    $stmt = mysqli_prepare($konek,
        "SELECT id, status, created_at FROM applications WHERE job_id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
    mysqli_stmt_execute($stmt);
    $lamaran = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
}

// peserta = pemilik ATAU helper yang pernah diterima di job ini.
// lamaran accepted tetap ACCEPTED saat job COMPLETED; jadi CANCELLED saat
// job dibatalkan — keduanya tetap peserta (riwayat tidak dihapus).
 $isPeserta = $isPemilik ||
             ($isHelper && $lamaran &&
              in_array($lamaran["status"], ["ACCEPTED", "CANCELLED"], true));

// jumlah pelamar (untuk pemilik)
 $jmlPelamar = 0;
if ($isPemilik) {
    $stmt = mysqli_prepare($konek,
        "SELECT COUNT(*) AS n FROM applications WHERE job_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $jmlPelamar = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))["n"];
    mysqli_stmt_close($stmt);
}

// ulasan job ini (hanya jika COMPLETED)
 $reviews  = [];
 $myReview = null;
if ($job["status"] === "COMPLETED") {
    $stmt = mysqli_prepare($konek,
        "SELECT reviews.reviewer_id, reviews.rating, reviews.komentar, reviews.created_at,
                users.nama AS reviewer_nama
         FROM reviews
         INNER JOIN users ON users.id = reviews.reviewer_id
         WHERE reviews.job_id = ?
         ORDER BY reviews.created_at ASC");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $reviews = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    if ($isPeserta) {
        foreach ($reviews as $r) {
            if ((int)$r["reviewer_id"] === (int)$_SESSION["user_id"]) {
                $myReview = $r;
                break;
            }
        }
    }
}

 $appliedBaru  = isset($_GET["applied"]);
 $reviewedBaru = isset($_GET["reviewed"]);
 $sudahUlasan  = isset($_GET["sudah"]);

 $judul_halaman = $job["judul"] . " — Tolongin!";
include 'includes/header.php';
?>

<main>
    <section class="page-content">
        <article class="job-card job-detail">

            <div class="card-top">
                <span class="badge badge-<?php echo strtolower(e($job["status"])); ?>">
                    <?php echo e($job["status"]); ?>
                </span>
            </div>

            <h2><?php echo e($job["judul"]); ?></h2>

            <ul>
                <li><?php echo e($job["lokasi"]); ?></li>
                <li><?php echo date("d M Y, H:i", strtotime($job["tanggal"])); ?></li>
                <li>Imbalan: <span class="reward"><?php echo formatRupiah($job["imbalan"]); ?></span></li>
                <li>Dibuat oleh: <?php echo e($job["nama_pemilik"] ?? "—"); ?></li>
            </ul>

            <?php if (trim($job["deskripsi"] ?? "") !== ""): ?>
                <h3>Deskripsi</h3>
                <p><?php echo e($job["deskripsi"]); ?></p>
            <?php endif; ?>

            <?php if ($appliedBaru && $lamaran): ?>
                <p class="success">Lamaran terkirim. Menunggu keputusan requester.</p>
            <?php endif; ?>

            <?php if ($reviewedBaru && $myReview): ?>
                <p class="success">Ulasan terkirim. Terima kasih!</p>
            <?php endif; ?>

            <?php if ($sudahUlasan): ?>
                <p class="notice">Kamu sudah pernah memberi ulasan untuk job ini.</p>
            <?php endif; ?>

            <!-- ===== area guest ===== -->
            <?php if (!isLoggedIn()): ?>
                <div class="job-actions">
                    <p class="notice"><a href="login.php">Masuk</a> sebagai helper untuk mengajukan diri.</p>
                </div>
            <?php endif; ?>

            <!-- ===== area helper ===== -->
            <?php if ($isHelper): ?>
                <div class="job-actions">
                    <?php if ($lamaran): ?>
                        <p class="notice">
                            Lamaranmu:
                            <span class="badge badge-<?php echo strtolower(e($lamaran["status"])); ?>">
                                <?php echo e($lamaran["status"]); ?>
                            </span>
                            — dikirim <?php echo date("d M Y", strtotime($lamaran["created_at"])); ?>
                        </p>
                    <?php elseif ($job["status"] === "OPEN"): ?>
                        <form method="post" action="apply.php">
                            <input type="hidden" name="job_id" value="<?php echo (int)$job["id"]; ?>">
                            <button type="submit" class="btn">Ajukan Diri</button>
                        </form>
                    <?php else: ?>
                        <p class="notice">Job ini tidak lagi menerima pelamar.</p>
                    <?php endif; ?>

                    <p class="apply-link"><a href="lamaran.php">Lamaran Saya</a></p>
                </div>
            <?php endif; ?>

            <!-- ===== area pemilik ===== -->
            <?php if ($isPemilik): ?>
                <div class="job-actions">
                    <?php if ($job["status"] === "OPEN"): ?>
                        <a href="edit.php?id=<?php echo (int)$job["id"]; ?>" class="btn">Edit</a>
                    <?php endif; ?>

                    <a href="pelamar.php?id=<?php echo (int)$job["id"]; ?>" class="btn btn-sm">
                        Pelamar (<?php echo $jmlPelamar; ?>)
                    </a>

                    <?php if ($job["status"] === "ACCEPTED"): ?>
                        <form method="post" action="status.php">
                            <input type="hidden" name="job_id" value="<?php echo (int)$job["id"]; ?>">
                            <input type="hidden" name="status_baru" value="IN_PROGRESS">
                            <button type="submit" class="btn">Mulai Kerja</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($job["status"] === "IN_PROGRESS"): ?>
                        <form method="post" action="status.php">
                            <input type="hidden" name="job_id" value="<?php echo (int)$job["id"]; ?>">
                            <input type="hidden" name="status_baru" value="COMPLETED">
                            <button type="submit" class="btn">Tandai Selesai</button>
                        </form>
                    <?php endif; ?>

                    <?php if (in_array($job["status"], ["OPEN", "ACCEPTED", "IN_PROGRESS"], true)): ?>
                        <form method="post" action="status.php">
                            <input type="hidden" name="job_id" value="<?php echo (int)$job["id"]; ?>">
                            <input type="hidden" name="status_baru" value="CANCELLED">
                            <button type="submit" class="btn btn-danger">Batalkan</button>
                        </form>
                    <?php endif; ?>

                    <?php if (in_array($job["status"], ["OPEN", "CANCELLED"], true)): ?>
                        <form method="post" action="hapus.php">
                            <input type="hidden" name="id" value="<?php echo (int)$job["id"]; ?>">
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </form>
                    <?php endif; ?>
                </div>

                <?php if (in_array($job["status"], ["COMPLETED", "CANCELLED"], true)): ?>
                    <p class="notice">Job ini sudah selesai — tersimpan sebagai riwayat.</p>
                <?php endif; ?>

                <!-- tombol ulasan untuk PEMILIK -->
                <?php if ($job["status"] === "COMPLETED"): ?>
                    <div class="job-actions">
                        <?php if ($myReview): ?>
                            <p class="notice">Ulasanmu sudah terkirim — terima kasih!</p>
                        <?php else: ?>
                            <a href="review.php?id=<?php echo (int)$job["id"]; ?>" class="btn">Beri Ulasan</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- tombol ulasan untuk HELPER peserta -->
            <?php if ($job["status"] === "COMPLETED" && $isPeserta && !$isPemilik): ?>
                <div class="job-actions">
                    <?php if ($myReview): ?>
                        <p class="notice">Ulasanmu sudah terkirim — terima kasih!</p>
                    <?php else: ?>
                        <a href="review.php?id=<?php echo (int)$job["id"]; ?>" class="btn">Beri Ulasan</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- ===== daftar ulasan (publik) ===== -->
            <?php if ($job["status"] === "COMPLETED"): ?>
                <h3>Ulasan</h3>

                <?php if (count($reviews) === 0): ?>
                    <p class="notice">Belum ada ulasan untuk job ini.</p>
                <?php else: ?>
                    <div class="review-list">
                        <?php foreach ($reviews as $r): ?>
                            <div class="review-item">
                                <div class="review-head">
                                    <span class="review-name"><?php echo e($r["reviewer_nama"]); ?></span>
                                    <?php echo bintang($r["rating"]); ?>
                                    <span class="review-date">
                                        <?php echo date("d M Y", strtotime($r["created_at"])); ?>
                                    </span>
                                </div>
                                <?php if (trim($r["komentar"] ?? "") !== ""): ?>
                                    <p><?php echo e($r["komentar"]); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </article>
    </section>
</main>

<?php include 'includes/footer.php'; ?>