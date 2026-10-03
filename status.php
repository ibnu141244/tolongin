<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/functions.php';

requireLogin();

 $job_id      = (int)($_POST["job_id"] ?? 0);
 $status_baru = $_POST["status_baru"] ?? "";

// whitelist target: hanya tiga status yang dicapai lewat aksi manual
if ($job_id <= 0 ||
    !in_array($status_baru, ["IN_PROGRESS", "COMPLETED", "CANCELLED"], true)) {
    header("Location: index.php");
    exit;
}

// job harus ada DAN milik session ini
 $stmt = mysqli_prepare($konek, "SELECT status FROM jobs WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $job_id, $_SESSION["user_id"]);
mysqli_stmt_execute($stmt);
 $job = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$job) {
    header("Location: index.php");
    exit;
}

 $status_lama = $job["status"];

// cek peta transisi: dari status sekarang, target ini legal?
if (!in_array($status_baru, transisiStatusDiizinkan()[$status_lama] ?? [], true)) {
    header("Location: job.php?id=" . $job_id);
    exit;
}

if ($status_baru === "CANCELLED") {

    // pembatalan menyentuh DUA tabel → transaksi
    mysqli_begin_transaction($konek);

    $ok = true;

    // 1. job → CANCELLED
    if ($ok) {
        $stmt = mysqli_prepare($konek, "UPDATE jobs SET status = 'CANCELLED' WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $job_id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // 2. lamaran yang masih PENDING atau sudah ACCEPTED → CANCELLED
    if ($ok) {
        $stmt = mysqli_prepare($konek,
            "UPDATE applications SET status = 'CANCELLED'
             WHERE job_id = ? AND status IN ('PENDING', 'ACCEPTED')");
        mysqli_stmt_bind_param($stmt, "i", $job_id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    if ($ok) {
        mysqli_commit($konek);
    } else {
        mysqli_rollback($konek);
    }

} else {

    // IN_PROGRESS dan COMPLETED hanya menyentuh satu tabel → satu UPDATE
    $stmt = mysqli_prepare($konek, "UPDATE jobs SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $status_baru, $job_id);

    if (!mysqli_stmt_execute($stmt)) {
        die("Gagal mengubah status: " . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);
}

header("Location: job.php?id=" . $job_id);
exit;