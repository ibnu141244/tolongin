<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/functions.php';

requireLogin();

 $judul_halaman = "Edit Job";
 $errors = [];

 $id = (int)($_GET["id"] ?? $_POST["id"] ?? 0);

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// ambil data lama
 $stmt = mysqli_prepare($konek, "SELECT * FROM jobs WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
 $job = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$job) {
    header("Location: index.php");
    exit;
}

// satpam 1: hanya pemilik
if ((int)$job["user_id"] !== (int)$_SESSION["user_id"]) {
    http_response_code(403);
    die("<h1>403 — Akses ditolak</h1><p>Job ini bukan milikmu.</p>");
}

// satpam 2: hanya job OPEN yang bisa diedit (M14 — kesepakatan tidak boleh diubah sepihak)
if ($job["status"] !== "OPEN") {
    http_response_code(403);
    die("<h1>403 — Tidak bisa diubah</h1><p>Job yang sudah melampaui status OPEN tidak bisa diedit.</p>");
}

// nilai awal dari database
 $judul     = $job["judul"];
 $lokasi    = $job["lokasi"];
 $deskripsi = $job["deskripsi"] ?? "";
 $tanggal   = date("Y-m-d\TH:i", strtotime($job["tanggal"]));
 $imbalan   = (float)$job["imbalan"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $judul     = trim($_POST["judul"] ?? "");
    $lokasi    = trim($_POST["lokasi"] ?? "");
    $deskripsi = trim($_POST["deskripsi"] ?? "");
    $tanggal   = trim($_POST["tanggal"] ?? "");
    $imbalan   = trim($_POST["imbalan"] ?? "");

    $errors = validasiJob($_POST);

    if (empty($errors)) {
        $tanggalSql = str_replace("T", " ", $tanggal);
        if (strlen($tanggalSql) === 16) {
            $tanggalSql .= ":00";
        }

        $sql = "UPDATE jobs
                SET judul = ?, lokasi = ?, deskripsi = ?, tanggal = ?, imbalan = ?
                WHERE id = ?";

        $stmt = mysqli_prepare($konek, $sql);

        // 6 tanda tanya = 6 variabel = 6 huruf: s s s s d i
        mysqli_stmt_bind_param($stmt, "ssssdi",
            $judul, $lokasi, $deskripsi, $tanggalSql, $imbalan, $id);

        if (!mysqli_stmt_execute($stmt)) {
            die("Gagal menyimpan perubahan: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);

        header("Location: job.php?id=" . $id);
        exit;
    }
}

include 'includes/header.php';
?>

<main>
    <div class="form-panel">
        <h2>Edit Job</h2>

        <?php foreach ($errors as $error): ?>
            <p class="error"><?php echo e($error); ?></p>
        <?php endforeach; ?>

        <form method="post" action="">
            <input type="hidden" name="id" value="<?php echo (int)$id; ?>">

            <div class="form-group">
                <label for="judul">Judul</label>
                <input type="text" id="judul" name="judul"
                       value="<?php echo e($judul); ?>">
            </div>

            <div class="form-group">
                <label for="lokasi">Lokasi</label>
                <input type="text" id="lokasi" name="lokasi"
                       value="<?php echo e($lokasi); ?>">
            </div>

            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="4"><?php echo e($deskripsi); ?></textarea>
            </div>

            <div class="form-group">
                <label for="tanggal">Tanggal &amp; Jam</label>
                <input type="datetime-local" id="tanggal" name="tanggal"
                       value="<?php echo e($tanggal); ?>">
            </div>

            <div class="form-group">
                <label for="imbalan">Imbalan (Rp)</label>
                <input type="number" id="imbalan" name="imbalan" step="1000"
                       value="<?php echo e($imbalan); ?>">
            </div>

            <button type="submit" class="btn">Simpan Perubahan</button>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>