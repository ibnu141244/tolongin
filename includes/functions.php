<?php

function formatRupiah($angka)
{
    return "Rp" . number_format((float)$angka, 0, ",", ".");
}

function e($teks)
{
    return htmlspecialchars($teks ?? "", ENT_QUOTES, "UTF-8");
}

function validasiJob($data)
{
    $errors = [];

    if (trim($data["judul"] ?? "") === "") {
        $errors[] = "Judul wajib diisi.";
    }
    if (trim($data["lokasi"] ?? "") === "") {
        $errors[] = "Lokasi wajib diisi.";
    }
    if (trim($data["tanggal"] ?? "") === "") {
        $errors[] = "Tanggal & jam wajib diisi.";
    }

    $imbalan = $data["imbalan"] ?? "";
    if ($imbalan === "" || !is_numeric($imbalan) || (float)$imbalan < 0) {
        $errors[] = "Imbalan harus berupa angka yang tidak negatif.";
    }

    return $errors;
}
function validasiRegister($data)
{
    $errors = [];

    if (trim($data["nama"] ?? "") === "") {
        $errors[] = "Nama wajib diisi.";
    }

    $email = trim($data["email"] ?? "");
    if ($email === "") {
        $errors[] = "Email wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }

    $password = $data["password"] ?? "";
    if (strlen($password) < 8) {
        $errors[] = "Password minimal 8 karakter.";
    }
    if (($data["password2"] ?? "") !== $password) {
        $errors[] = "Konfirmasi password tidak sama.";
    }

    $role = $data["role"] ?? "";
    if (!in_array($role, ["requester", "helper"], true)) {
        $errors[] = "Pilih status: requester atau helper.";
    }

    return $errors;
}
function validasiLogin($data)
{
    $errors = [];

    if (trim($data["email"] ?? "") === "" || ($data["password"] ?? "") === "") {
        $errors[] = "Email dan password wajib diisi.";
    }

    return $errors;
}
function transisiStatusDiizinkan(): array
{
    return [
        // OPEN → ACCEPTED TIDAK ada di sini: transisi itu terjadi
        // lewat keputusan.php (menerima pelamar), bukan aksi manual.
        "OPEN"        => ["CANCELLED"],
        "ACCEPTED"    => ["IN_PROGRESS", "CANCELLED"],
        "IN_PROGRESS" => ["COMPLETED", "CANCELLED"],
        "COMPLETED"   => [],
        "CANCELLED"   => [],
    ];
}
function validasiReview($data)
{
    $errors = [];

    $rating = $data["rating"] ?? "";
    if (!in_array($rating, ["1", "2", "3", "4", "5"], true)) {
        $errors[] = "Pilih rating dari 1 sampai 5.";
    }

    if (isset($data["komentar"]) && strlen(trim($data["komentar"])) > 500) {
        $errors[] = "Komentar maksimal 500 karakter.";
    }

    return $errors;
}

function bintang($rating)
{
    $rating = max(0, min(5, (int)$rating));
    return '<span class="stars">' .
           str_repeat("★", $rating) .
           str_repeat("☆", 5 - $rating) .
           '</span>';
}