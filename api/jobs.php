<?php
require __DIR__ . "/_api.php";

// ============================================================
// GET: daftar job OPEN (publik) — dengan pencarian & pagination
// ============================================================
if ($_SERVER["REQUEST_METHOD"] === "GET") {

    $q     = trim($_GET["q"] ?? "");
    $page  = max(1, (int)($_GET["page"] ?? 1));
    $limit = 6;

    $conditions = ["status = ?"];
    $params     = ["OPEN"];
    $types      = "s";

    if ($q !== "") {
        $conditions[] = "(judul LIKE ? OR deskripsi LIKE ?)";
        $params[]     = "%" . $q . "%";
        $params[]     = "%" . $q . "%";
        $types       .= "ss";
    }

    $where = "WHERE " . implode(" AND ", $conditions);

    // total (filter sama — konsep pagination M14.5, persis)
    $stmt = mysqli_prepare($konek, "SELECT COUNT(*) AS total FROM jobs $where");
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))["total"];
    mysqli_stmt_close($stmt);

    $totalPages = max(1, (int)ceil($total / $limit));
    $page       = min($page, $totalPages);
    $offset     = ($page - 1) * $limit;

    $stmt = mysqli_prepare($konek,
        "SELECT id, judul, lokasi, deskripsi, tanggal, imbalan, status, created_at
         FROM jobs $where
         ORDER BY created_at DESC
         LIMIT $limit OFFSET $offset");
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    // API mengirim DATA BERSIH: angka sebagai angka.
    // (formatRupiah dsb. adalah urusan tampilan — urusan client)
    foreach ($rows as &$r) {
        $r["id"]      = (int)$r["id"];
        $r["imbalan"] = (float)$r["imbalan"];
    }
    unset($r);

    json_response([
        "success" => true,
        "data"    => $rows,
        "meta"    => [
            "total"       => $total,
            "page"        => $page,
            "total_pages" => $totalPages,
            "per_page"    => $limit,
        ],
    ], 200);
}

// ============================================================
// POST: buat job (butuh token; hanya requester)
// ============================================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 401: siapa kamu? (authentication)
    $user = api_user();
    if (!$user) {
        json_response([
            "success" => false,
            "message" => "Tidak terautentikasi. Kirim header: Authorization: Bearer <token>",
        ], 401);
    }

    // 403: aku tahu kamu, tapi tidak boleh (authorization)
    if ($user["role"] !== "requester") {
        json_response([
            "success" => false,
            "message" => "Hanya requester yang bisa membuat job.",
        ], 403);
    }

    $body = api_body();

    // validasi memakai function yang SAMA dengan web — satu sumber kebenaran
    $errors = validasiJob($body);
    if (!empty($errors)) {
        json_response([
            "success" => false,
            "message" => implode(" ", $errors),
        ], 422);
    }

    $tanggalSql = str_replace("T", " ", trim($body["tanggal"]));
    if (strlen($tanggalSql) === 16) {
        $tanggalSql .= ":00";
    }

    $judul     = trim($body["judul"]);
    $lokasi    = trim($body["lokasi"]);
    $deskripsi = trim($body["deskripsi"] ?? "");
    $imbalan   = (float)$body["imbalan"];
    $user_id   = (int)$user["id"];   // identitas dari token — bukan dari body!

    $stmt = mysqli_prepare($konek,
        "INSERT INTO jobs (judul, lokasi, deskripsi, tanggal, imbalan, user_id)
         VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssssdi",
        $judul, $lokasi, $deskripsi, $tanggalSql, $imbalan, $user_id);

    if (!mysqli_stmt_execute($stmt)) {
        json_response([
            "success" => false,
            "message" => "Gagal menyimpan: " . mysqli_stmt_error($stmt),
        ], 500);
    }

    $newId = mysqli_insert_id($konek); // id yang barusan dibuat AUTO_INCREMENT
    mysqli_stmt_close($stmt);

    json_response([
        "success" => true,
        "message" => "Job berhasil dibuat.",
        "data"    => ["id" => (int)$newId],
    ], 201);
}

// method lain → 405
json_response([
    "success" => false,
    "message" => "Method tidak diizinkan.",
], 405);