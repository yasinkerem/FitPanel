<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function girisYapildiMi() {
    return isset($_SESSION['kullanici_id']);
}

function adminMi() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

function girisGerekli() {
    if (!girisYapildiMi()) {
        header('Location: login.php');
        exit;
    }
}

function adminGerekli() {
    girisGerekli();

    if (!adminMi()) {
        header('Location: kullanici_panel.php');
        exit;
    }
}

function kullaniciGerekli() {
    girisGerekli();

    if (adminMi()) {
        header('Location: admin_panel.php');
        exit;
    }
}