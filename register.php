<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/functions.php';

 $judul_halaman = "Daftar — Tolongin!";
 $errors = [];

 $nama  = "";
 $email = "";
 $role  = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // password sengaja TIDAK di-trim: spasi boleh jadi bagian sah password.
    // Kalau di-trim saat register tapi tidak saat login, user terkunci sendiri.
    $nama      = trim($_POST["nama"] ?? "");
    $email     = trim($_POST["email"] ?? "");
    $password  = $_POST["password"] ?? "";
    $password2 = $_POST["password2"] ?? "";
    $role      = $_POST["role"] ?? "";

    $errors = validasiRegister($_POST);

    // Lapis 1: cek email unik (demi pesan error yang ramah)
    if (empty($errors)) {
        $stmt = mysqli_prepare($konek, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $hasil = mysqli_stmt_get_result($stmt);

        if (mysqli_fetch_assoc($hasil)) {
            $errors[] = "Email sudah terdaftar. Gunakan email lain.";
        }
        mysqli_stmt_close($stmt);
    }

    // Lolos semua → simpan
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($konek, "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $hash, $role);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: login.php");
        exit;
    }
}

include 'includes/header.php';
?>

<main>
    <div class="form-panel">
        <h2>Daftar Tolongin!</h2>

        <?php foreach ($errors as $error): ?>
            <p class="error"><?php echo e($error); ?></p>
        <?php endforeach; ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="nama">Nama lengkap</label>
                <input type="text" id="nama" name="nama" value="<?php echo e($nama); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo e($email); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password (minimal 8 karakter)</label>
                <input type="password" id="password" name="password">
            </div>

            <div class="form-group">
                <label for="password2">Ulangi password</label>
                <input type="password" id="password2" name="password2">
            </div>

            <div class="form-group">
                <label>Daftar sebagai</label>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="role" value="requester"
                            <?php echo ($role === "requester") ? "checked" : ""; ?>>
                        Requester — saya butuh bantuan
                    </label>
                    <label>
                        <input type="radio" name="role" value="helper"
                            <?php echo ($role === "helper") ? "checked" : ""; ?>>
                        Helper — saya siap membantu
                    </label>
                </div>
            </div>

            <button type="submit" class="btn">Daftar</button>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>