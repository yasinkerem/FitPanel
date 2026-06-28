<?php
// ============================================================
// register.php - Kullanıcı Kayıt Sayfası
// Doğrulama: boşluk, format, min uzunluk, DB tekrar kontrolü
// Şifre: password_hash ile kaydedilir
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

$hatalar = [];
$form    = ['ad_soyad' => '', 'kullanici_adi' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Gelen verileri temizle
    $ad_soyad      = trim($_POST['ad_soyad']      ?? '');
    $kullanici_adi = trim($_POST['kullanici_adi']  ?? '');
    $email         = trim($_POST['email']          ?? '');
    $sifre         = $_POST['sifre']               ?? '';
    $sifre_tekrar  = $_POST['sifre_tekrar']        ?? '';

    // Form değerlerini geri doldurmak için sakla
    $form = [
        'ad_soyad'      => htmlspecialchars($ad_soyad),
        'kullanici_adi' => htmlspecialchars($kullanici_adi),
        'email'         => htmlspecialchars($email),
    ];

    // ---- DOĞRULAMA ----

    // Ad Soyad
    if (empty($ad_soyad)) {
        $hatalar[] = 'Ad Soyad alanı boş bırakılamaz.';
    } elseif (ctype_digit(str_replace(' ', '', $ad_soyad))) {
        $hatalar[] = 'Ad Soyad yalnızca rakamlardan oluşamaz.';
    }

    // Kullanıcı Adı
    if (empty($kullanici_adi)) {
        $hatalar[] = 'Kullanıcı Adı alanı boş bırakılamaz.';
    } elseif (mb_strlen($kullanici_adi) < 3) {
        $hatalar[] = 'Kullanıcı Adı en az 3 karakter olmalıdır.';
    }

    // E-posta
    if (empty($email)) {
        $hatalar[] = 'E-posta alanı boş bırakılamaz.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $hatalar[] = 'Geçerli bir e-posta adresi giriniz.';
    }

    // Şifre
    if (empty($sifre)) {
        $hatalar[] = 'Şifre alanı boş bırakılamaz.';
    } elseif (strlen($sifre) < 6) {
        $hatalar[] = 'Şifre en az 6 karakter olmalıdır.';
    }

    // Şifre tekrar
    if ($sifre !== $sifre_tekrar) {
        $hatalar[] = 'Şifre ve şifre tekrarı eşleşmiyor.';
    }

    // Veritabanı kontrolleri (format hatası yoksa)
    if (empty($hatalar)) {
        $pdo = baglan();

        // E-posta tekrar kontrolü
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $hatalar[] = 'Bu e-posta adresi zaten kayıtlı.';
        }

        // Kullanıcı adı tekrar kontrolü
        $stmt = $pdo->prepare('SELECT id FROM users WHERE kullanici_adi = ?');
        $stmt->execute([$kullanici_adi]);
        if ($stmt->fetch()) {
            $hatalar[] = 'Bu kullanıcı adı zaten alınmış.';
        }

        // Kayıt işlemi
        if (empty($hatalar)) {
            // Şifreyi hashle — açık metin ASLA veritabanına yazılmaz
            $sifre_hash = password_hash($sifre, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                'INSERT INTO users (ad_soyad, kullanici_adi, email, sifre, rol)
                 VALUES (?, ?, ?, ?, \'user\')'
            );
            $stmt->execute([$ad_soyad, $kullanici_adi, $email, $sifre_hash]);

            // Başarılı kayıt — login sayfasına yönlendir
            header('Location: login.php?kayit=basarili');
            exit;
        }
    }
}

$sayfa_basligi = 'Kayıt Ol';
$css_yolu      = 'assets/css/style.css';
require_once 'includes/header.php';
?>

<section class="auth-sayfa">
    <div class="auth-kutu" style="max-width: 520px;">
        <div class="auth-baslik">
            <div class="logo-buyuk">🏋️ FitPanel</div>
            <h2>Spor Salonu Üyeliği</h2>
            <p>Formu doldurun ve üyelik avantajlarından yararlanın.</p>
        </div>

        <?php if (!empty($hatalar)): ?>
            <div class="alert alert-hata">
                <?php foreach ($hatalar as $hata): ?>
                    <div>• <?= $hata ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" novalidate>

            <div class="form-grup">
                <label for="ad_soyad">Ad Soyad</label>
                <input type="text" id="ad_soyad" name="ad_soyad"
                       value="<?= $form['ad_soyad'] ?>"
                       placeholder="Adınızı ve soyadınızı girin">
            </div>

            <div class="form-grup">
                <label for="kullanici_adi">Kullanıcı Adı <small>(en az 3 karakter)</small></label>
                <input type="text" id="kullanici_adi" name="kullanici_adi"
                       value="<?= $form['kullanici_adi'] ?>"
                       placeholder="Kullanıcı adınızı belirleyin">
            </div>

            <div class="form-grup">
                <label for="email">E-posta</label>
                <input type="email" id="email" name="email"
                       value="<?= $form['email'] ?>"
                       placeholder="ornek@email.com">
            </div>

            <div class="form-grup">
                <label for="sifre">Şifre <small>(en az 6 karakter)</small></label>
                <input type="password" id="sifre" name="sifre"
                       placeholder="Şifrenizi girin">
            </div>

            <div class="form-grup">
                <label for="sifre_tekrar">Şifre Tekrar</label>
                <input type="password" id="sifre_tekrar" name="sifre_tekrar"
                       placeholder="Şifrenizi tekrar girin">
            </div>

            <button type="submit" class="btn btn-vurgu btn-tam">Kayıt Ol</button>
        </form>

        <div class="auth-alt">
            Zaten üyelerin var mı? <a href="login.php">Giriş yap</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
