<?php
require __DIR__ . "/_api.php";

// hanya POST yang sah di endpoint ini
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    json_response([
        "success" => false,
        "message" => "Method tidak diizinkan. Gunakan POST.",
    ], 405);
}

 $body = api_body();

 $email    = trim($body["email"] ?? "");
 $password = $body["password"] ?? "";

if ($email === "" || $password === "") {
    json_response([
        "success" => false,
        "message" => "Email dan password wajib diisi.",
    ], 422);
}

// Langkah 1: cari user berdasar EMAIL saja (konsep M8, persis)
 $stmt = mysqli_prepare($konek,
    "SELECT id, nama, email, password, role FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
 $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

// Langkah 2: verifikasi password di PHP — pesan generik (konsep M8, persis)
if (!$user || !password_verify($password, $user["password"])) {
    json_response([
        "success" => false,
        "message" => "Email atau password salah.",
    ], 401);
}

// token acak yang aman secara kriptografis: 32 byte acak → 64 karakter hex
 $token = bin2hex(random_bytes(32));

// simpan (menimpa token lama — satu token aktif per user; login baru = token lama mati)
 $stmt = mysqli_prepare($konek, "UPDATE users SET api_token = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "si", $token, $user["id"]);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

json_response([
    "success" => true,
    "message" => "Login berhasil.",
    "data" => [
        "token" => $token,
        "user" => [
            "id"    => (int)$user["id"],
            "nama"  => $user["nama"],
            "email" => $user["email"],
            "role"  => $user["role"],
        ],
    ],
], 200);