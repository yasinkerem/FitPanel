<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

if (isset($_SESSION['kullanici_id'])) {
    header('Location: ' . ($_SESSION['rol'] === 'admin' ? 'admin_panel.php' : 'kullanici_panel.php'));
    exit;
}

$hata      = '';
$basari    = '';
$email_val = '';

if (isset($_GET['kayit']) && $_GET['kayit'] === 'basarili') {
    $basari = 'Kayıt başarıyla tamamlandı. Giriş yapabilirsiniz.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email     = trim($_POST['email'] ?? '');
    $sifre     = $_POST['sifre'] ?? '';
    $email_val = htmlspecialchars($email);

    if (empty($email) || empty($sifre)) {
        $hata = 'E-posta ve şifre alanlarını doldurunuz.';
    } else {
        $pdo = baglan();

        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $kullanici = $stmt->fetch();

        if (!$kullanici) {
            $hata = 'Bu e-posta adresiyle kayıtlı bir hesap bulunamadı.';
        } elseif (!password_verify($sifre, $kullanici['sifre'])) {
            $hata = 'Şifre hatalı. Lütfen tekrar deneyin.';
        } else {
            $_SESSION['kullanici_id']  = $kullanici['id'];
            $_SESSION['ad_soyad']      = $kullanici['ad_soyad'];
            $_SESSION['kullanici_adi'] = $kullanici['kullanici_adi'];
            $_SESSION['email']         = $kullanici['email'];
            $_SESSION['rol']           = $kullanici['rol'];
            $_SESSION['kayit_tarihi']  = $kullanici['kayit_tarihi'];

            if ($kullanici['rol'] === 'admin') {
                header('Location: admin_panel.php');
            } else {
                header('Location: kullanici_panel.php');
            }
            exit;
        }
    }
}

$sayfa_basligi = 'Giriş Yap';
$css_yolu      = 'assets/css/style.css';
require_once 'includes/header.php';
?>

<div class="min-vh-100 d-flex align-items-center justify-content-center py-4"
     style="background: radial-gradient(ellipse at center, #1c2330 0%, #0d1117 70%);">
    <div class="w-100" style="max-width: 480px;">
        <div class="card border-secondary p-4 p-md-5" style="background:#1c2330;">

            <div class="text-center mb-4">
                <div class="text-success fw-bold fs-3 mb-1">🏋️ FitPanel</div>
                <h2 class="h4 text-white mb-1">Üye Girişi</h2>
                <p class="text-muted small mb-0">Spor salonu hesabınıza erişmek için giriş yapın.</p>
            </div>

            <?php if (!empty($hata)): ?>
                <div class="alert alert-danger"><?= $hata ?></div>
            <?php endif; ?>

            <?php if (!empty($basari)): ?>
                <div class="alert alert-success"><?= $basari ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label text-light">E-posta</label>
                    <input type="email" id="email" name="email"
                           class="form-control bg-dark text-light border-secondary"
                           value="<?= $email_val ?>"
                           placeholder="ornek@email.com">
                </div>

                <div class="mb-4">
                    <label for="sifre" class="form-label text-light">Şifre</label>
                    <input type="password" id="sifre" name="sifre"
                           class="form-control bg-dark text-light border-secondary"
                           placeholder="Şifrenizi girin">
                </div>

                <button type="submit" class="btn btn-success w-100">Giriş Yap</button>
            </form>

            <div class="auth-bilgi mt-4">
                <strong>Test Hesapları:</strong><br>
                Yönetici: <code>admin@fitpanel.com</code> / <code>admin123</code><br>
                Üye: <code>user@fitpanel.com</code> / <code>user123</code>
            </div>

            <div class="text-center mt-3 text-muted small">
                Henüz üye değil misin?
                <a href="register.php" class="text-success text-decoration-none">Üyelik oluştur</a>
            </div>

        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
