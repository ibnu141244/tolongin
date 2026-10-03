<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/functions.php';

requireRole("requester");

 $judul_halaman = "Buat Request — Tolongin!";

 $errors = [];

// nilai awal form (kosong; terisi lagi kalau validasi gagal)
 $judul    = "";
 $lokasi   = "";
 $deskripsi= "";
 $tanggal  = "";
 $imbalan  = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $judul     = trim($_POST["judul"] ?? "");
    $lokasi    = trim($_POST["lokasi"] ?? "");
    $deskripsi = trim($_POST["deskripsi"] ?? "");
    $tanggal   = trim($_POST["tanggal"] ?? "");
    $imbalan   = trim($_POST["imbalan"] ?? "");

    $errors = validasiJob($_POST);

    if (empty($errors)) {
        // format browser: "2026-09-30T14:00" → format MySQL: "2026-09-30 14:00:00"
        $tanggalSql = str_replace("T", " ", $tanggal);
        if (strlen($tanggalSql) === 16) {
            $tanggalSql .= ":00";
        }

        // identitas pemilik dari SESSION — bukan dari form
        $user_id = (int)$_SESSION["user_id"];

        $sql = "INSERT INTO jobs
                (judul, lokasi, deskripsi, tanggal, imbalan, user_id)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($konek, $sql);

        if (!$stmt) {
            die("Gagal menyiapkan query: " . mysqli_error($konek));
        }

        // 6 tanda tanya = 6 variabel = 6 huruf: s s s s d i
        mysqli_stmt_bind_param($stmt, "ssssdi",
            $judul, $lokasi, $deskripsi, $tanggalSql, $imbalan, $user_id);

        if (!mysqli_stmt_execute($stmt)) {
            die("Gagal menyimpan job: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);

        header("Location: index.php");
        exit;
    }
}

include 'includes/header.php';
?>

<main>
    <div class="form-panel">
        <h2>Buat Job Baru</h2>

        <?php foreach ($errors as $error): ?>
            <p class="error"><?php echo e($error); ?></p>
        <?php endforeach; ?>

        <form method="post" action="">
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

            <button type="submit" class="btn">Simpan Job</button>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>