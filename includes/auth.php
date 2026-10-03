<?php

// Konfigurasi cookie session — HARUS sebelum session_start()
session_set_cookie_params([
    "lifetime" => 0,        // cookie mati saat browser ditutup
    "path"     => "/",
    "httponly" => true,     // JavaScript dilarang membaca cookie ini
    "samesite" => "Lax",    // cookie hanya dikirim untuk request dari situs kita sendiri
]);

session_start();

function isLoggedIn(): bool
{
    return isset($_SESSION["user_id"]);
}

function currentUserRole(): ?string
{
    return $_SESSION["role"] ?? null;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

function requireRole(string $role): void
{
    requireLogin();

    if (currentUserRole() !== $role) {
        http_response_code(403);
        die("<h1>403 — Akses ditolak</h1><p>Halaman ini hanya untuk " . e($role) . ".</p>");
    }
}