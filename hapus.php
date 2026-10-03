<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/functions.php';

requireLogin();

 $id = (int)($_POST["id"] ?? 0);

if ($id > 0) {
     $stmt = mysqli_prepare($konek,
    "DELETE FROM jobs
     WHERE id = ? AND user_id = ? AND status IN ('OPEN', 'CANCELLED')");
    mysqli_stmt_bind_param($stmt, "ii", $id, $_SESSION["user_id"]);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

header("Location: index.php");
exit;