<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');

require_once 'includes/auth.php';
require_once 'config.php';

adminGerekli();

$pdo = baglan();

$hedef_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($hedef_id <= 0) {
    header('Location: admin_panel.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND rol = ?');
$stmt->execute([$hedef_id, 'user']);
$hedef_kullanici = $stmt->fetch();

if (!$hedef_kullanici) {
    header('Location: admin_panel.php');
    exit;
}

function kategoriClass($kat) {
    $map = [
        'Antrenman'  => 'antrenman',
        'Beslenme'   => 'beslenme',
        'Devamlılık' => 'devamlilik',
        'Performans' => 'performans',
    ];
    return $map[$kat] ?? 'diger';
}

$stmt = $pdo->prepare(
    'SELECT COUNT(*) as toplam,
            ROUND(AVG(puan),1) as ortalama,
            MAX(puan) as en_yuksek,
            MIN(puan) as en_dusuk,
            MAX(eklenme_tarihi) as son_tarih
     FROM puanlar WHERE user_id = ?'
);
$stmt->execute([$hedef_id]);
$istat = $stmt->fetch();

$toplam_kayit = (int)$istat['toplam'];
$ortalama     = $istat['ortalama'] ?? '—';
$en_yuksek    = $istat['en_yuksek'] ?? '—';
$en_dusuk     = $istat['en_dusuk']  ?? '—';
$son_tarih    = !empty($istat['son_tarih'])
    ? (new DateTime($istat['son_tarih']))->format('d.m.Y H:i')
    : '—';

$stmt = $pdo->prepare(
    'SELECT kategori, COUNT(*) as adet, ROUND(AVG(puan),1) as ort
     FROM puanlar WHERE user_id = ? GROUP BY kategori ORDER BY ort DESC'
);
$stmt->execute([$hedef_id]);
$kategori_istat = $stmt->fetchAll(PDO::FETCH_ASSOC);

$en_guclu_kat = !empty($kategori_istat) ? $kategori_istat[0] : null;
$en_zayif_kat = count($kategori_istat) > 1
    ? $kategori_istat[count($kategori_istat) - 1]
    : null;

$seviye      = '—';
$seviye_renk = 'mavi';
$oneri       = '';
$ort_float   = is_numeric($ortalama) ? (float)$ortalama : 0;

if ($toplam_kayit > 0) {
    if ($ort_float >= 85) {
        $seviye      = '🏆 Elit Sporcu';
        $seviye_renk = 'yesil';
        $oneri       = 'Mükemmel bir performans! Hedeflere ulaşmış görünüyor. Seviyesini korumaya devam etmeli.';
    } elseif ($ort_float >= 70) {
        $seviye      = '💪 Gelişmekte';
        $seviye_renk = 'mavi';
        $oneri       = 'İyi bir ilerleme kaydediyor. '
            . ($en_zayif_kat ? htmlspecialchars($en_zayif_kat['kategori']) . ' alanında' : 'Zayıf alanlarda')
            . ' ek destek sağlanabilir.';
    } elseif ($ort_float >= 50) {
        $seviye      = '📈 Başlangıç';
        $seviye_renk = 'turuncu';
        $oneri       = 'Gelişim potansiyeli mevcut. Düzenli program takibi ve motivasyon desteği önerilir.';
    } else {
        $seviye      = '🌱 Yeni Başlayan';
        $seviye_renk = 'kirmizi';
        $oneri       = 'Henüz başlangıç aşamasında. Antrenör rehberliği ve temel hedef belirleme tavsiye edilir.';
    }
}

$ad_soyad      = htmlspecialchars($hedef_kullanici['ad_soyad']);
$kullanici_adi = htmlspecialchars($hedef_kullanici['kullanici_adi']);
$email         = htmlspecialchars($hedef_kullanici['email']);
$kayit_tarihi  = !empty($hedef_kullanici['kayit_tarihi'])
    ? (new DateTime($hedef_kullanici['kayit_tarihi']))->format('d.m.Y H:i')
    : '—';

$sayfa_basligi  = 'Kullanıcı Detayı — ' . $ad_soyad;
$css_yolu       = 'assets/css/style.css';
$ana_sayfa_yolu = 'index.php';
$cikis_yolu     = 'logout.php';
require_once 'includes/header.php';
?>

<section class="py-4">
    <div class="container">

        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <h1 class="fw-bold mb-1" style="font-size:1.6rem;">🔍 Kullanıcı Detayı</h1>
                <p class="text-muted mb-0" style="font-size:0.92rem;"><?= $ad_soyad ?> kullanıcısının performans özeti</p>
            </div>
            <a href="admin_panel.php" class="btn btn-outline-secondary" id="geri-btn">← Admin Paneline Dön</a>
        </div>

        <div class="kart mb-4">
            <div class="kart-baslik">👤 Kullanıcı Bilgileri</div>
            <div class="bilgi-satiri d-flex justify-content-between border-bottom py-2 px-1">
                <span class="bilgi-etiket">Ad Soyad</span>
                <span class="bilgi-deger"><?= $ad_soyad ?></span>
            </div>
            <div class="bilgi-satiri d-flex justify-content-between border-bottom py-2 px-1">
                <span class="bilgi-etiket">Kullanıcı Adı</span>
                <span class="bilgi-deger">@<?= $kullanici_adi ?></span>
            </div>
            <div class="bilgi-satiri d-flex justify-content-between border-bottom py-2 px-1">
                <span class="bilgi-etiket">E-posta</span>
                <span class="bilgi-deger"><?= $email ?></span>
            </div>
            <div class="bilgi-satiri d-flex justify-content-between border-bottom py-2 px-1">
                <span class="bilgi-etiket">Üyelik Türü</span>
                <span class="bilgi-deger"><span class="rozet rozet-user">Standart Üye</span></span>
            </div>
            <div class="bilgi-satiri d-flex justify-content-between border-bottom py-2 px-1">
                <span class="bilgi-etiket">Kayıt Tarihi</span>
                <span class="bilgi-deger"><?= $kayit_tarihi ?></span>
            </div>
            <div class="bilgi-satiri d-flex justify-content-between py-2 px-1">
                <span class="bilgi-etiket">Son Değerlendirme</span>
                <span class="bilgi-deger"><?= $son_tarih ?></span>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="istat-kart mavi">
                    <div class="istat-ikon">📋</div>
                    <div class="istat-sayi"><?= $toplam_kayit ?></div>
                    <div class="istat-etiket">Toplam Değerlendirme</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="istat-kart yesil">
                    <div class="istat-ikon">⭐</div>
                    <div class="istat-sayi"><?= $ortalama ?></div>
                    <div class="istat-etiket">Genel Ortalama</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="istat-kart turuncu">
                    <div class="istat-ikon">🏆</div>
                    <div class="istat-sayi"><?= $en_yuksek ?></div>
                    <div class="istat-etiket">En Yüksek Puan</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="istat-kart kirmizi">
                    <div class="istat-ikon">📉</div>
                    <div class="istat-sayi"><?= $en_dusuk ?></div>
                    <div class="istat-etiket">En Düşük Puan</div>
                </div>
            </div>
        </div>

        <?php if ($toplam_kayit > 0): ?>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="kart h-100">
                    <div class="kart-baslik">🏅 Kategori Özeti</div>
                    <?php if ($en_guclu_kat): ?>
                    <div class="bilgi-satiri d-flex justify-content-between align-items-center border-bottom py-2 px-1">
                        <span class="bilgi-etiket">En Güçlü Kategori</span>
                        <span class="bilgi-deger">
                            <span class="rozet rozet-kategori rozet-<?= kategoriClass($en_guclu_kat['kategori']) ?>"><?= htmlspecialchars($en_guclu_kat['kategori']) ?></span>
                            <span style="color:var(--renk-basari); font-size:0.85rem; margin-left:6px;"><?= $en_guclu_kat['ort'] ?> / 100</span>
                        </span>
                    </div>
                    <?php endif; ?>
                    <?php if ($en_zayif_kat): ?>
                    <div class="bilgi-satiri d-flex justify-content-between align-items-center border-bottom py-2 px-1">
                        <span class="bilgi-etiket">En Zayıf Kategori</span>
                        <span class="bilgi-deger">
                            <span class="rozet rozet-kategori rozet-<?= kategoriClass($en_zayif_kat['kategori']) ?>"><?= htmlspecialchars($en_zayif_kat['kategori']) ?></span>
                            <span style="color:var(--renk-hata); font-size:0.85rem; margin-left:6px;"><?= $en_zayif_kat['ort'] ?> / 100</span>
                        </span>
                    </div>
                    <?php endif; ?>
                    <div class="bilgi-satiri d-flex justify-content-between align-items-center py-2 px-1">
                        <span class="bilgi-etiket">Gelişim Seviyesi</span>
                        <span class="bilgi-deger">
                            <span class="istat-kart-rozet rozet-seviye rozet-seviye-<?= $seviye_renk ?>"
                                  style="font-weight:700; font-size:0.9rem;"><?= $seviye ?></span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="kart h-100">
                    <div class="kart-baslik">💡 Gelişim Önerisi</div>
                    <p style="font-size:0.92rem; color:var(--renk-metin); line-height:1.7; margin:0;">
                        <?= htmlspecialchars($oneri) ?>
                    </p>
                </div>
            </div>
        </div>

        <?php if (!empty($kategori_istat)): ?>
        <div class="kart mb-4">
            <div class="kart-baslik">📊 Kategori Detay Tablosu</div>
            <div class="table-responsive tablo-kapsam">
                <table class="table table-hover uye-tablosu mb-0">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Kayıt Sayısı</th>
                            <th>Ortalama Puan</th>
                            <th>Performans</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kategori_istat as $ki): ?>
                        <tr>
                            <td>
                                <span class="rozet rozet-kategori rozet-<?= kategoriClass($ki['kategori']) ?>">
                                    <?= htmlspecialchars($ki['kategori']) ?>
                                </span>
                            </td>
                            <td><?= $ki['adet'] ?> kayıt</td>
                            <td>
                                <strong class="puan-goster <?= (float)$ki['ort'] >= 75 ? 'puan-iyi' : ((float)$ki['ort'] >= 50 ? 'puan-orta' : 'puan-dusuk') ?>">
                                    <?= $ki['ort'] ?>
                                </strong> / 100
                            </td>
                            <td style="min-width:140px;">
                                <div class="progress-bar-bg">
                                    <div class="progress-bar-dolgu <?= kategoriClass($ki['kategori']) ?>-bg"
                                         style="width:<?= $ki['ort'] ?>%"></div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="kart">
            <p class="bos-mesaj">🏋️ Bu kullanıcı henüz hiç performans değerlendirmesi yapmamış.</p>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
