<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/functions.php';

requireLogin();

 $judul_halaman = "Beri Ulasan — Tolongin!";
 $errors = [];
 $rating = "";
 $komentar = "";

 $job_id = (int)($_GET["id"] ?? $_POST["job_id"] ?? 0);
if ($job_id <= 0) {
    header("Location: index.php");
    exit;
}

// job harus ada
 $stmt = mysqli_prepare($konek, "SELECT * FROM jobs WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $job_id);
mysqli_stmt_execute($stmt);
 $job = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$job) {
    header("Location: index.php");
    exit;
}

// hanya job COMPLETED yang bisa diulas (aturan bisnis)
if ($job["status"] !== "COMPLETED") {
    header("Location: job.php?id=" . $job_id);
    exit;
}

 $user_id = (int)$_SESSION["user_id"];
 $isOwner = (int)$job["user_id"] === $user_id;

// ===== hitung LAWAN ulasan dari database — bukan dari form =====
 $reviewee_id = 0;

if ($isOwner) {
    // pemilik menilai helper yang diterima di job ini
    $stmt = mysqli_prepare($konek,
        "SELECT user_id FROM applications WHERE job_id = ? AND status = 'ACCEPTED'");
    mysqli_stmt_bind_param($stmt, "i", $job_id);
    mysqli_stmt_execute($stmt);
    $acc = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($acc) {
        $reviewee_id = (int)$acc["user_id"];
    }
} else {
    // apakah saya helper yang DITERIMA di job ini?
    $stmt = mysqli_prepare($konek,
        "SELECT id FROM applications WHERE job_id = ? AND user_id = ? AND status = 'ACCEPTED'");
    mysqli_stmt_bind_param($stmt, "ii", $job_id, $user_id);
    mysqli_stmt_execute($stmt);
    $acc = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($acc) {
        $reviewee_id = (int)$job["user_id"];
    }
}

// bukan peserta (atau helper belum terpilih) → tidak berhak mengulas
if ($reviewee_id <= 0) {
    http_response_code(403);
    die("<h1>403 — Akses ditolak</h1><p>Hanya peserta job yang bisa memberi ulasan.</p>");
}

// nama lawan, untuk heading form
 $stmt = mysqli_prepare($konek, "SELECT nama FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $reviewee_id);
mysqli_stmt_execute($stmt);
 $reviewee = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

// sudah pernah mengulas? (jalur ramah; tembok UNIQUE tetap di bawahnya)
 $stmt = mysqli_prepare($konek,
    "SELECT id FROM reviews WHERE job_id = ? AND reviewer_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $job_id, $user_id);
mysqli_stmt_execute($stmt);
 $sudah = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($sudah) {
    header("Location: job.php?id=" . $job_id . "&sudah=1");
    exit;
}

 $rating_label = [
    "1" => "Buruk",
    "2" => "Kurang",
    "3" => "Cukup",
    "4" => "Baik",
    "5" => "Sangat baik",
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $rating   = trim($_POST["rating"] ?? "");
    $komentar = trim($_POST["komentar"] ?? "");

    $errors = validasiReview($_POST);

        if (empty($errors)) {
        // FIX: cast disimpan ke variabel dulu — bind_param hanya menerima
        // variabel (by reference), bukan ekspresi seperti (int)$rating
        $ratingInt = (int)$rating;

        $stmt = mysqli_prepare($konek,
            "INSERT INTO reviews (job_id, reviewer_id, reviewee_id, rating, komentar)
             VALUES (?, ?, ?, ?, ?)");

        // reviewee_id dari SERVER (dihitung di atas) — tidak pernah dari form
        mysqli_stmt_bind_param($stmt, "iiiis",
            $job_id, $user_id, $reviewee_id, $ratingInt, $komentar);

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: job.php?id=" . $job_id . "&sudah=1");
            exit;
        }
        mysqli_stmt_close($stmt);

        header("Location: job.php?id=" . $job_id . "&reviewed=1");
        exit;
    }
}

include 'includes/header.php';
?>

<main>
    <div class="form-panel">
        <h2>Beri Ulasan</h2>

        <p class="app-meta" style="margin-bottom: 16px;">
            Job: <a href="job.php?id=<?php echo (int)$job_id; ?>"><?php echo e($job["judul"]); ?></a>
            · menilai <strong><?php echo e($reviewee["nama"] ?? "—"); ?></strong>
        </p>

        <?php foreach ($errors as $error): ?>
            <p class="error"><?php echo e($error); ?></p>
        <?php endforeach; ?>

        <form method="post" action="">
            <input type="hidden" name="job_id" value="<?php echo (int)$job_id; ?>">

            <div class="form-group">
                <label>Rating</label>
                <div class="star-rating">
                    <?php for ($nilai = 5; $nilai >= 1; $nilai--): ?>
                        <input type="radio" id="star<?php echo $nilai; ?>"
                               name="rating" value="<?php echo $nilai; ?>"
                               required
                               <?php echo ($rating === (string)$nilai) ? "checked" : ""; ?>>
                        <label for="star<?php echo $nilai; ?>">★</label>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="komentar">Komentar (opsional, maks 500 karakter)</label>
                <textarea id="komentar" name="komentar" rows="4"><?php echo e($komentar); ?></textarea>
            </div>

            <button type="submit" class="btn">Kirim Ulasan</button>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>