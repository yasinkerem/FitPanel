<?php
$sayfa_basligi = $sayfa_basligi ?? 'FitPanel';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FitPanel - Spor Salonu Üyelik ve Performans Takip Sistemi.">
    <title><?= htmlspecialchars($sayfa_basligi) ?> | FitPanel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $css_yolu ?? 'assets/css/style.css' ?>">
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= $ana_sayfa_yolu ?? 'index.php' ?>">
            🏋️ FitPanel
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <?php if (isset($_SESSION['kullanici_id'])): ?>
                    <li class="nav-item d-none d-lg-flex align-items-center">
                        <span class="text-muted me-2" style="font-size:0.88rem;">
                            👤 <?= htmlspecialchars($_SESSION['ad_soyad']) ?>
                            <span class="ms-1 <?= $_SESSION['rol'] === 'admin' ? 'rozet-admin' : 'rozet-user' ?>" style="font-size:0.72rem;">
                                <?= $_SESSION['rol'] === 'admin' ? 'Yönetici' : 'Üye' ?>
                            </span>
                        </span>
                    </li>
                    <li class="nav-item">
                        <?php if ($_SESSION['rol'] === 'admin'): ?>
                            <a href="<?= $admin_panel_yolu ?? 'admin_panel.php' ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-gear"></i> Yönetim
                            </a>
                        <?php else: ?>
                            <a href="<?= $kullanici_panel_yolu ?? 'kullanici_panel.php' ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-person-circle"></i> Panelim
                            </a>
                        <?php endif; ?>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $cikis_yolu ?? 'logout.php' ?>" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-box-arrow-right"></i> Çıkış
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="<?= $login_yolu ?? 'login.php' ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-box-arrow-in-right"></i> Üye Girişi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $register_yolu ?? 'register.php' ?>" class="btn btn-success btn-sm">
                            <i class="bi bi-person-plus"></i> Üye Ol
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main>
