<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/functions.php';

requireRole("helper");

 $job_id  = (int)($_POST["job_id"] ?? 0);
 $user_id = (int)$_SESSION["user_id"];

if ($job_id > 0) {

    // job harus ada dan masih OPEN
    $stmt = mysqli_prepare($konek, "SELECT id FROM jobs WHERE id = ? AND status = 'OPEN'");
    mysqli_stmt_bind_param($stmt, "i", $job_id);
    mysqli_stmt_execute($stmt);
    $job = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($job) {
        // Lapis 2: cek duplikat — demi alur yang ramah.
        // Lapis 3 (UNIQUE di DB) tetap tembok terakhir atas race condition.
        $stmt = mysqli_prepare($konek,
            "SELECT id FROM applications WHERE job_id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $job_id, $user_id);
        mysqli_stmt_execute($stmt);
        $sudah = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$sudah) {
            $stmt = mysqli_prepare($konek,
                "INSERT INTO applications (job_id, user_id) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, "ii", $job_id, $user_id);

            if (!mysqli_stmt_execute($stmt)) {
                die("Gagal mengirim lamaran: " . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);

            header("Location: job.php?id=" . $job_id . "&applied=1");
            exit;
        }
    }
}

// jalur gagal apa pun → pulang ke halaman job (state dihitung ulang di sana)
header("Location: job.php?id=" . $job_id);
exit;