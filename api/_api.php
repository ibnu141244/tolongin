<?php
// Bootstrap khusus API.
// Catatan penting: kita TIDAK memuat auth.php — API stateless, tidak ada session.
// Identitas datang dari token di header, dicek per request.

header("Content-Type: application/json; charset=utf-8");

// helper jawaban: set status code + cetak JSON + berhenti.
// dipakai semua endpoint agar bentuk respons seragam.
function json_response($data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

require __DIR__ . "/../includes/db.php";        // $konek
require __DIR__ . "/../includes/functions.php"; // validasiJob, dkk.

// baca user dari header "Authorization: Bearer <token>"
// mengembalikan array user, atau null kalau token hilang/salah
function api_user(): ?array
{
    global $konek;

    $header = $_SERVER["HTTP_AUTHORIZATION"] ?? "";

    // format header: "Bearer <token>" — regex menangkap bagian tokennya
    if (preg_match("/^Bearer\s+(.+)$/i", $header, $m)) {
        $token = trim($m[1]);

        $stmt = mysqli_prepare($konek,
            "SELECT id, nama, email, role FROM users WHERE api_token = ?");
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if ($user) {
            return $user;
        }
    }

    return null;
}

// baca body request sebagai array (untuk POST JSON)
function api_body(): array
{
    $raw = file_get_contents("php://input");
    return json_decode($raw, true) ?? [];
}