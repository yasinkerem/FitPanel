<?php
// ============================================================
// login.php - Kullanıcı Giriş Sayfası
// password_verify ile şifre kontrolü yapılır.
// Başarılı girişte session oluşturulur, role göre yönlendirilir.
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

// Zaten giriş yapmışsa yönlendir
if (isset($_SESSION['kullanici_id'])) {
    header('Location: ' . ($_SESSION['rol'] === 'admin' ? 'admin_panel.php' : 'kullanici_panel.php'));
    exit;
}

$hata      = '';
$basari    = '';
$email_val = '';

// Kayıt başarı mesajı
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

        // Kullanıcıyı e-posta ile bul (prepared statement)
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $kullanici = $stmt->fetch();

        if (!$kullanici) {
            $hata = 'Bu e-posta adresiyle kayıtlı bir hesap bulunamadı.';
        } elseif (!password_verify($sifre, $kullanici['sifre'])) {
            // password_verify: düz metin şifre ile hashlenmiş şifreyi karşılaştırır
            $hata = 'Şifre hatalı. Lütfen tekrar deneyin.';
        } else {
            // Giriş başarılı — session'a kullanıcı bilgilerini kaydet
            $_SESSION['kullanici_id']  = $kullanici['id'];
            $_SESSION['ad_soyad']      = $kullanici['ad_soyad'];
            $_SESSION['kullanici_adi'] = $kullanici['kullanici_adi'];
            $_SESSION['email']         = $kullanici['email'];
            $_SESSION['rol']           = $kullanici['rol'];
            $_SESSION['kayit_tarihi']  = $kullanici['kayit_tarihi'];

            // Role göre yönlendir
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

<section class="auth-sayfa">
    <div class="auth-kutu">
        <div class="auth-baslik">
            <div class="logo-buyuk">🏋️ FitPanel</div>
            <h2>Üye Girişi</h2>
            <p>Spor salonu hesabınıza erişmek için giriş yapın.</p>
        </div>

        <?php if (!empty($hata)): ?>
            <div class="alert alert-hata"><?= $hata ?></div>
        <?php endif; ?>

        <?php if (!empty($basari)): ?>
            <div class="alert alert-basari"><?= $basari ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php" novalidate>
            <div class="form-grup">
                <label for="email">E-posta</label>
                <input type="email" id="email" name="email"
                       value="<?= $email_val ?>"
                       placeholder="ornek@email.com">
            </div>

            <div class="form-grup">
                <label for="sifre">Şifre</label>
                <input type="password" id="sifre" name="sifre"
                       placeholder="Şifrenizi girin">
            </div>

            <button type="submit" class="btn btn-vurgu btn-tam">Giriş Yap</button>
        </form>

        <div class="auth-bilgi">
            <strong>Test Hesapları:</strong><br>
            Yönetici: <code>admin@fitpanel.com</code> / <code>admin123</code><br>
            Üye: <code>user@fitpanel.com</code> / <code>user123</code>
        </div>

        <div class="auth-alt">
            Henüz üye değil misin? <a href="register.php">Üyelik oluştur</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
