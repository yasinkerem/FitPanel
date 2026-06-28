<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');

require_once 'includes/auth.php';
require_once 'config.php';

girisGerekli();

if (adminMi()) {
    header('Location: admin_panel.php');
    exit;
}

$pdo          = baglan();
$kullanici_id = $_SESSION['kullanici_id'];
$ad_soyad     = htmlspecialchars($_SESSION['ad_soyad']);
$kullanici_adi = htmlspecialchars($_SESSION['kullanici_adi']);
$email         = htmlspecialchars($_SESSION['email']);
$kayit_tarihi  = $_SESSION['kayit_tarihi'] ?? '';
$kayit_tarihi_fmt = !empty($kayit_tarihi)
    ? (new DateTime($kayit_tarihi))->format('d.m.Y')
    : '—';
$rapor_tarihi = (new DateTime())->format('d.m.Y H:i');

$stmt = $pdo->prepare(
    'SELECT COUNT(*) as toplam,
            ROUND(AVG(puan),1) as ortalama,
            MAX(puan) as en_yuksek,
            MIN(puan) as en_dusuk,
            MAX(eklenme_tarihi) as son_tarih
     FROM puanlar WHERE user_id = ?'
);
$stmt->execute([$kullanici_id]);
$istat = $stmt->fetch();

$toplam_kayit = (int)$istat['toplam'];
$ortalama     = $istat['ortalama'] ?? 0;
$en_yuksek    = $istat['en_yuksek'] ?? 0;
$en_dusuk     = $istat['en_dusuk']  ?? 0;
$son_tarih    = !empty($istat['son_tarih'])
    ? (new DateTime($istat['son_tarih']))->format('d.m.Y H:i')
    : '—';

if ($toplam_kayit === 0) {
    header('Location: kullanici_panel.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT kategori, COUNT(*) as adet, ROUND(AVG(puan),1) as ort
     FROM puanlar WHERE user_id = ? GROUP BY kategori ORDER BY ort DESC'
);
$stmt->execute([$kullanici_id]);
$kategori_istat = $stmt->fetchAll(PDO::FETCH_ASSOC);

$en_guclu_kat = !empty($kategori_istat) ? $kategori_istat[0] : null;
$en_zayif_kat = !empty($kategori_istat) ? $kategori_istat[count($kategori_istat) - 1] : null;

$seviye      = '';
$seviye_renk = '';
$oneri       = '';
$ort_float   = (float)$ortalama;

if ($ort_float >= 85) {
    $seviye      = '🏆 Elit Sporcu';
    $seviye_renk = '#22c55e';
    $oneri       = 'Mükemmel bir performans sergiliyorsun! Hedeflerini bir üst seviyeye taşıma zamanı. Spor yaşam tarzı haline gelmiş — bu motivasyonu koru.';
} elseif ($ort_float >= 70) {
    $seviye      = '💪 Gelişmekte';
    $seviye_renk = '#3b82f6';
    $oneri       = 'İyi bir ilerleme gösteriyorsun! '
        . ($en_zayif_kat ? htmlspecialchars($en_zayif_kat['kategori']) . ' alanında' : 'Zayıf alanlarda')
        . ' daha fazla çalışarak bir üst seviyeye ulaşabilirsin.';
} elseif ($ort_float >= 50) {
    $seviye      = '📈 Başlangıç';
    $seviye_renk = '#f59e0b';
    $oneri       = 'Doğru yoldasın! Düzenli antrenman programı ve beslenme planı oluşturarak hızlıca gelişim sağlayabilirsin.';
} else {
    $seviye      = '🌱 Yeni Başlayan';
    $seviye_renk = '#f87171';
    $oneri       = 'Her büyük yolculuk küçük adımlarla başlar. Önce düzenli antrenman alışkanlığı kazanmaya odaklan. Haftada 3 gün küçük hedeflerle başla.';
}

function kategoriRenk($kat) {
    $map = [
        'Antrenman'  => '#3b82f6',
        'Beslenme'   => '#22c55e',
        'Devamlılık' => '#a855f7',
        'Performans' => '#f59e0b',
    ];
    return $map[$kat] ?? '#94a3b8';
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
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performans Raporu | FitPanel</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .seviye-rozet {
            display: inline-block;
            padding: 6px 20px;
            border-radius: 20px;
            font-size: 0.95rem;
            font-weight: 700;
            border: 2px solid;
        }
        @media print {
            .site-header,
            .site-footer,
            .rapor-butonlar {
                display: none !important;
            }
            body {
                background: #fff !important;
                color: #000 !important;
            }
            .kart {
                background: #fff !important;
                border: 1px solid #ccc !important;
                box-shadow: none !important;
                animation: none !important;
            }
            .kart-baslik { color: #1a1a1a !important; }
            .rapor-sayfa { padding: 0 !important; }
            .istat-kart { background: #f8f8f8 !important; border: 1px solid #ddd !important; }
            .istat-sayi { color: #1a1a1a !important; }
            .istat-etiket { color: #555 !important; }
            .bilgi-etiket { color: #555 !important; }
            .bilgi-deger { color: #111 !important; }
            .rozet { border: 1px solid #999 !important; }
            .progress-bar-bg { background: #ddd !important; }
        }
    </style>
</head>
<body>

<?php require_once 'includes/header.php'; ?>

<section class="py-4">
    <div class="container">

        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <h1 class="fw-bold mb-1" style="font-size:1.6rem;">📄 Performans Raporu</h1>
                <p class="text-muted mb-0" style="font-size:0.88rem;">Oluşturulma tarihi: <?= $rapor_tarihi ?></p>
            </div>
            <div class="rapor-butonlar d-flex gap-2 flex-wrap">
                <button onclick="window.print()" class="btn btn-success" id="yazdir-btn">🖨️ Yazdır / PDF</button>
                <a href="kullanici_panel.php" class="btn btn-outline-secondary">← Panele Dön</a>
            </div>
        </div>

        <div class="kart mb-4">
            <div class="kart-baslik">👤 Kullanıcı Bilgileri</div>
            <div class="row g-0">
                <div class="col-12 col-md-6">
                    <div class="bilgi-satiri d-flex justify-content-between border-bottom py-2 px-1">
                        <span class="bilgi-etiket">Ad Soyad</span>
                        <span class="bilgi-deger"><?= $ad_soyad ?></span>
                    </div>
                    <div class="bilgi-satiri d-flex justify-content-between border-bottom py-2 px-1">
                        <span class="bilgi-etiket">Kullanıcı Adı</span>
                        <span class="bilgi-deger">@<?= $kullanici_adi ?></span>
                    </div>
                    <div class="bilgi-satiri d-flex justify-content-between py-2 px-1">
                        <span class="bilgi-etiket">E-posta</span>
                        <span class="bilgi-deger"><?= $email ?></span>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="bilgi-satiri d-flex justify-content-between border-bottom py-2 px-1">
                        <span class="bilgi-etiket">Üyelik Başlangıcı</span>
                        <span class="bilgi-deger"><?= $kayit_tarihi_fmt ?></span>
                    </div>
                    <div class="bilgi-satiri d-flex justify-content-between py-2 px-1">
                        <span class="bilgi-etiket">Son Değerlendirme</span>
                        <span class="bilgi-deger"><?= $son_tarih ?></span>
                    </div>
                </div>
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
                    <?php if ($en_zayif_kat && $en_zayif_kat !== $en_guclu_kat): ?>
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
                            <span class="seviye-rozet" style="color:<?= $seviye_renk ?>; border-color:<?= $seviye_renk ?>; background:<?= $seviye_renk ?>1a;"><?= $seviye ?></span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="kart h-100">
                    <div class="kart-baslik">💡 Gelişim Önerisi</div>
                    <p style="font-size:0.92rem; color:var(--renk-metin); line-height:1.7; margin-bottom:0;">
                        <?= htmlspecialchars($oneri) ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="kart">
            <div class="kart-baslik">📊 Kategori Detay Tablosu</div>
            <?php if (!empty($kategori_istat)): ?>
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
                                <strong class="<?= (float)$ki['ort'] >= 75 ? 'puan-iyi' : ((float)$ki['ort'] >= 50 ? 'puan-orta' : 'puan-dusuk') ?> puan-goster">
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
            <?php endif; ?>
        </div>

    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
