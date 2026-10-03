<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/functions.php';

requireLogin();

 $application_id = (int)($_POST["application_id"] ?? 0);
 $aksi           = $_POST["aksi"] ?? "";

// whitelist aksi — nilai lain tidak berhak memicu apa pun
if (!in_array($aksi, ["terima", "tolak"], true) || $application_id <= 0) {
    header("Location: index.php");
    exit;
}

// verifikasi: lamaran ini harus berada di job milik session ini (JOIN = satpam)
 $stmt = mysqli_prepare($konek,
    "SELECT applications.status AS app_status,
            jobs.id   AS job_id,
            jobs.status AS job_status
     FROM applications
     INNER JOIN jobs ON jobs.id = applications.job_id
     WHERE applications.id = ? AND jobs.user_id = ?");

mysqli_stmt_bind_param($stmt, "ii", $application_id, $_SESSION["user_id"]);
mysqli_stmt_execute($stmt);
 $app = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$app) {
    header("Location: index.php");
    exit;
}

 $job_id = (int)$app["job_id"];

if ($aksi === "tolak") {

    // hanya PENDING yang bisa ditolak
    if ($app["app_status"] === "PENDING") {
        $stmt = mysqli_prepare($konek,
            "UPDATE applications SET status = 'REJECTED' WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $application_id);

        if (!mysqli_stmt_execute($stmt)) {
            die("Gagal memproses keputusan: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
    }

} else { // ===== terima =====

    // syarat: lamaran masih PENDING dan job masih OPEN
    if ($app["app_status"] === "PENDING" && $app["job_status"] === "OPEN") {

        // TRANSAKSI: tiga perubahan harus benar-benar satu paket
        mysqli_begin_transaction($konek);

        $ok = true;

        // 1. lamaran ini → ACCEPTED
        if ($ok) {
            $stmt = mysqli_prepare($konek,
                "UPDATE applications SET status = 'ACCEPTED' WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $application_id);
            $ok = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        // 2. semua PENDING lain di job ini → REJECTED
        //    (lamaran di atas kini berstatus ACCEPTED, jadi tidak ikut kena)
        if ($ok) {
            $stmt = mysqli_prepare($konek,
                "UPDATE applications SET status = 'REJECTED'
                 WHERE job_id = ? AND status = 'PENDING'");
            mysqli_stmt_bind_param($stmt, "i", $job_id);
            $ok = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        // 3. job → ACCEPTED
        if ($ok) {
            $stmt = mysqli_prepare($konek,
                "UPDATE jobs SET status = 'ACCEPTED' WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $job_id);
            $ok = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        if ($ok) {
            mysqli_commit($konek);      // benamkan ketiganya
        } else {
            mysqli_rollback($konek);    // batalkan semua — state tetap bersih
        }
    }
}

header("Location: pelamar.php?id=" . $job_id);
exit;