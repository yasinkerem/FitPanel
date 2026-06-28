<?php
// ============================================================
// logout.php - Çıkış işlemi
// Session tamamen temizlenir, cookie silinir, login'e yönlendirilir.
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tüm session değişkenlerini temizle
$_SESSION = [];

// Session cookie'sini sil
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Session'ı yok et
session_destroy();

// Giriş sayfasına yönlendir
header('Location: login.php');
exit;
