<?php
include 'includes/auth.php';

include 'includes/db.php';
include 'includes/functions.php';

 $judul_halaman = "Masuk — Tolongin!";
 $errors = [];
 $email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $errors = validasiLogin($_POST);

    if (empty($errors)) {
        // Langkah 1: cari user berdasarkan EMAIL saja
        $stmt = mysqli_prepare($konek, "SELECT * FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $hasil = mysqli_stmt_get_result($stmt);
        $user  = mysqli_fetch_assoc($hasil);
        mysqli_stmt_close($stmt);

        // Langkah 2: verifikasi password di PHP
        if (!$user || !password_verify($password, $user["password"])) {
            $errors[] = "Email atau password salah.";
        } else {
            session_regenerate_id(true);   // ← BARU: ganti ID session dengan yang baru
            $_SESSION["user_id"] = (int)$user["id"];
            $_SESSION["nama"]    = $user["nama"];
            $_SESSION["role"]    = $user["role"];
            header("Location: index.php");
         exit;
        }
    }
}

include 'includes/header.php';
?>

<main>
    <div class="form-panel">
        <h2>Masuk Tolongin!</h2>

        <?php foreach ($errors as $error): ?>
            <p class="error"><?php echo e($error); ?></p>
        <?php endforeach; ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo e($email); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
            </div>

            <button type="submit" class="btn">Masuk</button>
        </form>

        <p style="margin-top:16px;">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    </div>
</main>

<?php include 'includes/footer.php'; ?>