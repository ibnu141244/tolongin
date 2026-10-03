<?php
include 'includes/auth.php';

// 1. kosongkan isi loker
session_unset();

// 2. hancurkan loker-nya (file sess_xxx di server dihapus)
session_destroy();

// 3. suruh browser buang kuncinya juga
if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), "", time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
}

header("Location: index.php");
exit;