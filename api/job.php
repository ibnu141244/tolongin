<?php
require __DIR__ . "/_api.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    json_response([
        "success" => false,
        "message" => "Method tidak diizinkan. Gunakan GET.",
    ], 405);
}

 $id = (int)($_GET["id"] ?? 0);

if ($id <= 0) {
    json_response([
        "success" => false,
        "message" => "Parameter id wajib angka yang valid.",
    ], 422);
}

 $stmt = mysqli_prepare($konek,
    "SELECT jobs.id, jobs.judul, jobs.lokasi, jobs.deskripsi,
            jobs.tanggal, jobs.imbalan, jobs.status, jobs.created_at,
            users.nama AS pemilik
     FROM jobs
     LEFT JOIN users ON users.id = jobs.user_id
     WHERE jobs.id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
 $job = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$job) {
    json_response([
        "success" => false,
        "message" => "Job dengan id $id tidak ditemukan.",
    ], 404);
}

 $job["id"]      = (int)$job["id"];
 $job["imbalan"] = (float)$job["imbalan"];

json_response([
    "success" => true,
    "data"    => $job,
], 200);