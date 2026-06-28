<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

if (isset($_SESSION['kullanici_id'])) {
    header('Location: ' . ($_SESSION['rol'] === 'admin' ? 'admin_panel.php' : 'kullanici_panel.php'));
    exit;
}

$hatalar = [];
$form    = ['ad_soyad' => '', 'kullanici_adi' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ad_soyad      = trim($_POST['ad_soyad']      ?? '');
    $kullanici_adi = trim($_POST['kullanici_adi']  ?? '');
    $email         = trim($_POST['email']          ?? '');
    $sifre         = $_POST['sifre']               ?? '';
    $sifre_tekrar  = $_POST['sifre_tekrar']        ?? '';

    $form = [
        'ad_soyad'      => htmlspecialchars($ad_soyad),
        'kullanici_adi' => htmlspecialchars($kullanici_adi),
        'email'         => htmlspecialchars($email),
    ];

    if (empty($ad_soyad)) {
        $hatalar[] = 'Ad Soyad alanı boş bırakılamaz.';
    } elseif (ctype_digit(str_replace(' ', '', $ad_soyad))) {
        $hatalar[] = 'Ad Soyad yalnızca rakamlardan oluşamaz.';
    }

    if (empty($kullanici_adi)) {
        $hatalar[] = 'Kullanıcı Adı alanı boş bırakılamaz.';
    } elseif (mb_strlen($kullanici_adi) < 3) {
        $hatalar[] = 'Kullanıcı Adı en az 3 karakter olmalıdır.';
    }

    if (empty($email)) {
        $hatalar[] = 'E-posta alanı boş bırakılamaz.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $hatalar[] = 'Geçerli bir e-posta adresi giriniz.';
    }

    if (empty($sifre)) {
        $hatalar[] = 'Şifre alanı boş bırakılamaz.';
    } elseif (strlen($sifre) < 6) {
        $hatalar[] = 'Şifre en az 6 karakter olmalıdır.';
    }

    if ($sifre !== $sifre_tekrar) {
        $hatalar[] = 'Şifre ve şifre tekrarı eşleşmiyor.';
    }

    if (empty($hatalar)) {
        $pdo = baglan();

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $hatalar[] = 'Bu e-posta adresi zaten kayıtlı.';
        }

        $stmt = $pdo->prepare('SELECT id FROM users WHERE kullanici_adi = ?');
        $stmt->execute([$kullanici_adi]);
        if ($stmt->fetch()) {
            $hatalar[] = 'Bu kullanıcı adı zaten alınmış.';
        }

        if (empty($hatalar)) {
            $sifre_hash = password_hash($sifre, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO users (ad_soyad, kullanici_adi, email, sifre, rol)
                 VALUES (?, ?, ?, ?, 'user')"
            );
            $stmt->execute([$ad_soyad, $kullanici_adi, $email, $sifre_hash]);

            header('Location: login.php?kayit=basarili');
            exit;
        }
    }
}

$sayfa_basligi = 'Kayıt Ol';
$css_yolu      = 'assets/css/style.css';
require_once 'includes/header.php';
?>

<div class="min-vh-100 d-flex align-items-center justify-content-center py-4"
     style="background: radial-gradient(ellipse at center, #1c2330 0%, #0d1117 70%);">
    <div class="w-100" style="max-width: 520px;">
        <div class="card p-4 p-md-5" style="background:#1c2330; border:1px solid #30363d;">

            <div class="text-center mb-4">
                <div class="text-success fw-bold fs-3 mb-1">🏋️ FitPanel</div>
                <h2 class="fw-bold text-white mb-1">Spor Salonu Üyeliği</h2>
                <p class="text-muted mb-0">Formu doldurun ve üyelik avantajlarından yararlanın.</p>
            </div>

            <?php if (!empty($hatalar)): ?>
                <div class="alert alert-danger py-2">
                    <?php foreach ($hatalar as $hata): ?>
                        <div>• <?= $hata ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php" novalidate>

                <div class="mb-3">
                    <label for="ad_soyad" class="form-label text-white-50">Ad Soyad</label>
                    <input type="text" id="ad_soyad" name="ad_soyad"
                           class="form-control bg-dark text-white border-secondary"
                           value="<?= $form['ad_soyad'] ?>"
                           placeholder="Adınızı ve soyadınızı girin">
                </div>

                <div class="mb-3">
                    <label for="kullanici_adi" class="form-label text-white-50">
                        Kullanıcı Adı <small class="text-muted">(en az 3 karakter)</small>
                    </label>
                    <input type="text" id="kullanici_adi" name="kullanici_adi"
                           class="form-control bg-dark text-white border-secondary"
                           value="<?= $form['kullanici_adi'] ?>"
                           placeholder="Kullanıcı adınızı belirleyin">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label text-white-50">E-posta</label>
                    <input type="email" id="email" name="email"
                           class="form-control bg-dark text-white border-secondary"
                           value="<?= $form['email'] ?>"
                           placeholder="ornek@email.com">
                </div>

                <div class="mb-3">
                    <label for="sifre" class="form-label text-white-50">
                        Şifre <small class="text-muted">(en az 6 karakter)</small>
                    </label>
                    <input type="password" id="sifre" name="sifre"
                           class="form-control bg-dark text-white border-secondary"
                           placeholder="Şifrenizi girin">
                </div>

                <div class="mb-4">
                    <label for="sifre_tekrar" class="form-label text-white-50">Şifre Tekrar</label>
                    <input type="password" id="sifre_tekrar" name="sifre_tekrar"
                           class="form-control bg-dark text-white border-secondary"
                           placeholder="Şifrenizi tekrar girin">
                </div>

                <button type="submit" class="btn btn-success w-100 fw-semibold">Kayıt Ol</button>
            </form>

            <div class="text-center mt-3 text-muted small">
                Zaten üyeliğin var mı?
                <a href="login.php" class="text-success text-decoration-none fw-semibold">Giriş yap</a>
            </div>

        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
