<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config.php';
try {
    $pdo_idx = baglan();
    $istat_uye    = (int) $pdo_idx->query("SELECT COUNT(*) FROM users WHERE rol = 'user'")->fetchColumn();
    $istat_toplam = (int) $pdo_idx->query("SELECT COUNT(*) FROM puanlar")->fetchColumn();
    $istat_ort    = (float)($pdo_idx->query("SELECT ROUND(AVG(puan),1) FROM puanlar")->fetchColumn() ?? 0);
    $istat_en_iyi = (int)($pdo_idx->query("SELECT MAX(puan) FROM puanlar")->fetchColumn() ?? 0);
} catch (Exception $e) {
    $istat_uye = $istat_toplam = $istat_en_iyi = 0;
    $istat_ort = 0;
}
$sayfa_basligi = 'Anasayfa';
$css_yolu      = 'assets/css/style.css';
require_once 'includes/header.php';
?>

<section class="hero">
    <div class="container text-center">
        <div class="hero-badge">🏋️ Spor Salonu Üyelik Sistemi</div>
        <h1>FitPanel ile <span>Sporun</span> Bir Üst Seviyeye Taşı</h1>
        <p class="hero-alt">
            Spor salonumuza kayıt ol, antrenman performansını takip et.
            Antrenörler ve yöneticiler tüm üye verilerini anlık olarak izler.
        </p>
        <?php if (!isset($_SESSION['kullanici_id'])): ?>
        <div class="hero-butonlar d-flex flex-wrap justify-content-center gap-3">
            <a href="register.php" class="btn btn-success btn-lg">Üyelik Başlat</a>
            <a href="login.php" class="btn btn-outline-success btn-lg">Üye Girişi</a>
        </div>
        <?php else: ?>
        <div class="hero-butonlar d-flex flex-wrap justify-content-center gap-3">
            <?php if ($_SESSION['rol'] === 'admin'): ?>
                <a href="admin_panel.php" class="btn btn-success btn-lg">⚙️ Yönetim Paneli</a>
            <?php else: ?>
                <a href="kullanici_panel.php" class="btn btn-success btn-lg">💪 Üye Panelime Git</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="canli-istat-bolumu">
    <div class="container">
        <div class="row g-4 justify-content-center">
            <div class="col-6 col-md-3 text-center">
                <span class="canli-istat-ikon">👥</span>
                <div class="canli-istat-sayi" data-hedef="<?= $istat_uye ?>">0</div>
                <div class="canli-istat-etiket">Kayıtlı Üye</div>
            </div>
            <div class="col-6 col-md-3 text-center">
                <span class="canli-istat-ikon">📊</span>
                <div class="canli-istat-sayi" data-hedef="<?= $istat_toplam ?>">0</div>
                <div class="canli-istat-etiket">Performans Kaydı</div>
            </div>
            <div class="col-6 col-md-3 text-center">
                <span class="canli-istat-ikon">⭐</span>
                <div class="canli-istat-sayi" data-hedef="<?= $istat_en_iyi ?>" data-suffix="">0</div>
                <div class="canli-istat-etiket">En Yüksek Puan</div>
            </div>
            <div class="col-6 col-md-3 text-center">
                <span class="canli-istat-ikon">🏋️</span>
                <div class="canli-istat-sayi" data-hedef="4">0</div>
                <div class="canli-istat-etiket">Değerlendirme Kategorisi</div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="bolum-baslik text-center mb-5">
            <h2>Nasıl Çalışır?</h2>
            <p>Birkaç adımda üyeliğinizi başlatın ve takibinize başlayın.</p>
            <div class="cizgi"></div>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="alan-kart h-100">
                    <div class="alan-ikon">📝</div>
                    <h3>Üye Ol</h3>
                    <p>Ad soyad, kullanıcı adı ve e-posta ile üyelik oluşturun. Bilgileriniz güvenli şekilde şifrelenerek saklanır.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="alan-kart h-100">
                    <div class="alan-ikon">🔐</div>
                    <h3>Giriş Yap</h3>
                    <p>E-posta ve şifrenizle giriş yapın. Sistem rolünüzü kontrol ederek sizi doğru panele yönlendirir.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="alan-kart h-100">
                    <div class="alan-ikon">📊</div>
                    <h3>Performansını Değerlendir</h3>
                    <p>Antrenman, beslenme, devamlılık ve performans kategorilerinde haftalık değerlendirme yap. Puanın otomatik hesaplanır.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5" style="background-color: var(--renk-yuzey); border-top: 1px solid var(--renk-sinir); border-bottom: 1px solid var(--renk-sinir);">
    <div class="container">
        <div class="bolum-baslik text-center mb-5">
            <h2>Üyelik Avantajları</h2>
            <p>FitPanel üyeleri için özel takip ve analiz özellikleri.</p>
            <div class="cizgi"></div>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-6">
                <div class="paket-kart h-100">
                    <div class="paket-ikon">👤</div>
                    <h3>Standart Üye</h3>
                    <ul class="paket-ozellikler">
                        <li>Kişisel üye paneline erişim</li>
                        <li>Haftalık performans değerlendirmesi</li>
                        <li>4 kategoride puan takibi</li>
                        <li>Antrenman geçmişini görüntüleme</li>
                        <li>Motivasyon mesajları ve istatistikler</li>
                        <li>Kategori bazlı ilerleme grafikleri</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="paket-kart one-cikan h-100">
                    <span class="paket-rozet">Yönetici</span>
                    <div class="paket-ikon">⚙️</div>
                    <h3>Antrenör / Admin</h3>
                    <ul class="paket-ozellikler">
                        <li>Tüm üyeleri listeler ve izler</li>
                        <li>Tüm puan kayıtlarını görür</li>
                        <li>Sistem geneli istatistikler</li>
                        <li>Kategori bazlı performans analizi</li>
                        <li>Üye kayıt tarihlerini görüntüler</li>
                        <li>Genel ortalama ve en yüksek puan takibi</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="bolum-baslik text-center mb-5">
            <h2>🏆 Değerlendirme Kategorileri</h2>
            <p>4 temel kategori ile fitness gelişiminizi kapsamlı takip edin.</p>
            <div class="cizgi"></div>
        </div>
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="alan-kart h-100">
                    <div class="alan-ikon">🏋️</div>
                    <h3>Antrenman</h3>
                    <p>Haftalık antrenman günü, süre, yoğunluk ve plana bağlılık değerlendirmesi.</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="alan-kart h-100">
                    <div class="alan-ikon">🥗</div>
                    <h3>Beslenme</h3>
                    <p>Öğün düzeni, su tüketimi, fast food kaçınma ve protein hedefi takibi.</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="alan-kart h-100">
                    <div class="alan-ikon">🔁</div>
                    <h3>Devamlılık</h3>
                    <p>Aylık düzenlilik, ara verme süresi, motivasyon ve hedefe kararlılık ölçümü.</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="alan-kart h-100">
                    <div class="alan-ikon">📈</div>
                    <h3>Performans</h3>
                    <p>Kondisyon gelişimi, kişisel rekorlar, ağırlık artışı ve toparlanma süresi.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5" style="background-color: var(--renk-yuzey); border-top: 1px solid var(--renk-sinir);">
    <div class="container">
        <div class="bolum-baslik text-center mb-5">
            <h2>🛠 Güvenli Altyapı</h2>
            <p>Üyelerimizin verileri en yüksek güvenlik standartlarıyla korunur.</p>
            <div class="cizgi"></div>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="alan-kart h-100">
                    <div class="alan-ikon">🔒</div>
                    <h3>Şifreli Kayıt</h3>
                    <p>Tüm şifreler bcrypt algoritmasıyla hashlenerek saklanır. Hiçbir zaman açık metin tutulmaz.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="alan-kart h-100">
                    <div class="alan-ikon">🛡️</div>
                    <h3>Güvenli Sorgular</h3>
                    <p>SQL Injection saldırılarına karşı PDO Prepared Statement kullanılır.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="alan-kart h-100">
                    <div class="alan-ikon">🔑</div>
                    <h3>Rol Bazlı Erişim</h3>
                    <p>Üye ve yönetici rolleriyle her kullanıcı yalnızca kendi yetkisindeki verilere erişir.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    var sayaclar = document.querySelectorAll('.canli-istat-sayi[data-hedef]');
    if (!sayaclar.length) return;

    function sayiAnimasyon(el, hedef, sure) {
        var baslangic = 0;
        var adim = sure / 60;
        var artis = hedef / (sure / (1000 / 60));
        var interval = setInterval(function () {
            baslangic += artis;
            if (baslangic >= hedef) {
                el.textContent = hedef;
                clearInterval(interval);
            } else {
                el.textContent = Math.floor(baslangic);
            }
        }, adim);
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting && !entry.target.dataset.baslatildi) {
                entry.target.dataset.baslatildi = '1';
                var hedef = parseInt(entry.target.dataset.hedef, 10) || 0;
                sayiAnimasyon(entry.target, hedef, 1800);
            }
        });
    }, { threshold: 0.3 });

    sayaclar.forEach(function (el) { observer.observe(el); });
})();
</script>

<?php require_once 'includes/footer.php'; ?>
