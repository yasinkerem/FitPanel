<?php
// Sayfa başlığı her sayfadan aktarılabilir
$sayfa_basligi = $sayfa_basligi ?? 'FitPanel';

// Session başlat (header'da da gerekli)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FitPanel - Spor Salonu Üyelik ve Performans Takip Sistemi. Üyeliğinizi yönetin, antrenman performansınızı takip edin.">
    <title><?= htmlspecialchars($sayfa_basligi) ?> | FitPanel Spor Salonu</title>
    <link rel="stylesheet" href="<?= $css_yolu ?? 'assets/css/style.css' ?>">
</head>
<body>

<header class="site-header">
    <div class="container header-ici">
        <a href="<?= $ana_sayfa_yolu ?? 'index.php' ?>" class="logo">
            <span class="logo-icon">🏋️</span> FitPanel
        </a>
        <nav class="header-nav">
            <?php if (isset($_SESSION['kullanici_id'])): ?>
                <span class="hosgeldin-yazi">
                    👤 <?= htmlspecialchars($_SESSION['ad_soyad']) ?>
                    <span class="rol-rozet <?= $_SESSION['rol'] === 'admin' ? 'rozet-admin' : 'rozet-user' ?>">
                        <?= $_SESSION['rol'] === 'admin' ? 'Yönetici' : 'Üye' ?>
                    </span>
                </span>
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                    <a href="<?= $admin_panel_yolu ?? 'admin_panel.php' ?>" class="btn btn-kucuk">⚙️ Yönetim</a>
                <?php else: ?>
                    <a href="<?= $kullanici_panel_yolu ?? 'kullanici_panel.php' ?>" class="btn btn-kucuk">💪 Panelim</a>
                <?php endif; ?>
                <a href="<?= $cikis_yolu ?? 'logout.php' ?>" class="btn btn-kucuk btn-cikis">Çıkış</a>
            <?php else: ?>
                <a href="<?= $login_yolu ?? 'login.php' ?>" class="btn btn-kucuk">Üye Girişi</a>
                <a href="<?= $register_yolu ?? 'register.php' ?>" class="btn btn-kucuk btn-vurgu">Üye Ol</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="sayfa-ici">
