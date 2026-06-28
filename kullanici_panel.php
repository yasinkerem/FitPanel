<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');

require_once 'includes/auth.php';
require_once 'config.php';

kullaniciGerekli();

$pdo = baglan();

function kategoriClass($kat) {
    $map = [
        'Antrenman'  => 'antrenman',
        'Beslenme'   => 'beslenme',
        'Devamlılık' => 'devamlilik',
        'Performans' => 'performans',
    ];
    return $map[$kat] ?? 'diger';
}

$sorular = [
    'Antrenman' => [
        ['soru' => 'Bu hafta kaç gün antrenman yaptın?', 'secenekler' => [
            ['metin' => 'Hiç yapmadım',   'puan' => 0],
            ['metin' => '1–2 gün',         'puan' => 25],
            ['metin' => '3–4 gün',         'puan' => 70],
            ['metin' => '5 gün ve üzeri', 'puan' => 100],
        ]],
        ['soru' => 'Ortalama antrenman süren ne kadar?', 'secenekler' => [
            ['metin' => '15 dakikadan az', 'puan' => 10],
            ['metin' => '15–30 dakika',    'puan' => 40],
            ['metin' => '30–60 dakika',    'puan' => 75],
            ['metin' => "60 dk'dan fazla", 'puan' => 100],
        ]],
        ['soru' => 'Antrenman yoğunluğunu nasıl değerlendirirsin?', 'secenekler' => [
            ['metin' => 'Çok hafif', 'puan' => 10],
            ['metin' => 'Orta',      'puan' => 45],
            ['metin' => 'Yoğun',     'puan' => 75],
            ['metin' => 'Çok yoğun','puan' => 100],
        ]],
        ['soru' => 'Antrenman planına ne kadar bağlı kaldın?', 'secenekler' => [
            ['metin' => 'Hiç uymadım',               'puan' => 0],
            ['metin' => 'Kısmen uydum',               'puan' => 40],
            ['metin' => 'Büyük çoğunluğunu yaptım',  'puan' => 75],
            ['metin' => 'Tam olarak uydum',           'puan' => 100],
        ]],
    ],
    'Beslenme' => [
        ['soru' => 'Bugün kaç öğün yedin?', 'secenekler' => [
            ['metin' => '1 öğün',                       'puan' => 20],
            ['metin' => '2 öğün',                       'puan' => 50],
            ['metin' => '3 öğün',                       'puan' => 80],
            ['metin' => '4+ öğün (düzenli aralıklarla)','puan' => 100],
        ]],
        ['soru' => 'Günlük su tüketimin ne kadar?', 'secenekler' => [
            ['metin' => '1 litreden az',   'puan' => 10],
            ['metin' => '1–2 litre',       'puan' => 50],
            ['metin' => '2–3 litre',       'puan' => 80],
            ['metin' => '3 litre ve üzeri','puan' => 100],
        ]],
        ['soru' => 'İşlenmiş gıda / fast food tüketin?', 'secenekler' => [
            ['metin' => 'Her öğünde tükettim',    'puan' => 0],
            ['metin' => 'Birkaç kez tükettim',    'puan' => 30],
            ['metin' => 'Sadece bir kez tükettim','puan' => 65],
            ['metin' => 'Hiç tüketmedim',         'puan' => 100],
        ]],
        ['soru' => 'Protein tüketimine ne kadar dikkat ettin?', 'secenekler' => [
            ['metin' => 'Hiç dikkat etmedim',              'puan' => 10],
            ['metin' => 'Biraz dikkat ettim',              'puan' => 40],
            ['metin' => 'Yeterli protein aldım',           'puan' => 75],
            ['metin' => 'Günlük protein hedefimi karşıladım','puan' => 100],
        ]],
    ],
    'Devamlılık' => [
        ['soru' => 'Bu ay kaç hafta düzenli antrenman yaptın?', 'secenekler' => [
            ['metin' => 'Hiç',              'puan' => 0],
            ['metin' => '1 hafta',          'puan' => 25],
            ['metin' => '2–3 hafta',        'puan' => 65],
            ['metin' => '4 hafta (tüm ay)','puan' => 100],
        ]],
        ['soru' => 'En uzun ara verdiğin süre ne kadar?', 'secenekler' => [
            ['metin' => '2 haftadan fazla',               'puan' => 0],
            ['metin' => '1–2 hafta',                      'puan' => 30],
            ['metin' => '3–7 gün',                        'puan' => 65],
            ['metin' => '1–2 gün veya hiç ara vermedim', 'puan' => 100],
        ]],
        ['soru' => 'Genel motivasyon durumun nasıl?', 'secenekler' => [
            ['metin' => 'Çok düşük, bırakmayı düşünüyorum','puan' => 5],
            ['metin' => 'Düşük',                           'puan' => 35],
            ['metin' => 'İyi',                             'puan' => 70],
            ['metin' => 'Çok yüksek',                     'puan' => 100],
        ]],
        ['soru' => 'Hedeflerine ulaşma konusundaki kararlılığın?', 'secenekler' => [
            ['metin' => 'Vazgeçmeyi düşünüyorum','puan' => 5],
            ['metin' => 'Kararsızım',             'puan' => 35],
            ['metin' => 'Kararlıyım',             'puan' => 70],
            ['metin' => 'Çok kararlıyım',         'puan' => 100],
        ]],
    ],
    'Performans' => [
        ['soru' => 'Önceki haftaya kıyasla kondisyonun nasıl?', 'secenekler' => [
            ['metin' => 'Geriledi',                   'puan' => 10],
            ['metin' => 'Aynı kaldı',                 'puan' => 40],
            ['metin' => 'Biraz iyileşti',             'puan' => 70],
            ['metin' => 'Belirgin şekilde iyileşti', 'puan' => 100],
        ]],
        ['soru' => 'Bu hafta kişisel rekor kırdın mı?', 'secenekler' => [
            ['metin' => 'Hayır',            'puan' => 20],
            ['metin' => 'Bir kez kırdım',   'puan' => 55],
            ['metin' => 'Birkaç kez kırdım','puan' => 80],
            ['metin' => 'Sık sık kırdım',   'puan' => 100],
        ]],
        ['soru' => 'Egzersizlerde ağırlık / mesafe / süre artırdın mı?', 'secenekler' => [
            ['metin' => 'Hayır, düşürdüm',           'puan' => 10],
            ['metin' => 'Aynı kaldım',               'puan' => 35],
            ['metin' => 'Biraz artırdım',            'puan' => 70],
            ['metin' => 'Önemli miktarda artırdım',  'puan' => 100],
        ]],
        ['soru' => 'Antrenman sonrası toparlanma süren nasıl?', 'secenekler' => [
            ['metin' => 'Çok uzun sürüyor, bitkinim', 'puan' => 15],
            ['metin' => 'Normalden uzun sürüyor',      'puan' => 40],
            ['metin' => 'Normal',                      'puan' => 70],
            ['metin' => 'Çok hızlı toparlanıyorum',  'puan' => 100],
        ]],
    ],
];

$kategoriler = array_keys($sorular);

$kullanici_id  = $_SESSION['kullanici_id'];
$ad_soyad      = htmlspecialchars($_SESSION['ad_soyad']);
$kullanici_adi = htmlspecialchars($_SESSION['kullanici_adi']);
$email         = htmlspecialchars($_SESSION['email']);
$kayit_tarihi  = $_SESSION['kayit_tarihi'] ?? '';
$kayit_tarihi_fmt = '';
if (!empty($kayit_tarihi)) {
    $kayit_tarihi_fmt = (new DateTime($kayit_tarihi))->format('d.m.Y');
}

$form_hata   = '';
$form_basari = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['puan_ekle'])) {
    $kategori = trim($_POST['kategori'] ?? '');
    $aciklama = trim($_POST['aciklama'] ?? '');

    if (!isset($sorular[$kategori])) {
        $form_hata = 'Geçerli bir kategori seçin.';
    } else {
        $soru_listesi = $sorular[$kategori];
        $soru_sayisi  = count($soru_listesi);
        $toplam       = 0;
        $gecerli      = true;

        for ($i = 0; $i < $soru_sayisi; $i++) {
            $cevap = isset($_POST["soru_$i"]) ? (int)$_POST["soru_$i"] : -1;
            if ($cevap < 0 || $cevap >= count($soru_listesi[$i]['secenekler'])) {
                $form_hata = 'Lütfen tüm soruları cevaplayın.';
                $gecerli = false;
                break;
            }
            $toplam += $soru_listesi[$i]['secenekler'][$cevap]['puan'];
        }

        if ($gecerli) {
            $hesaplanan_puan = (int)round($toplam / $soru_sayisi);

            $stmt = $pdo->prepare(
                'INSERT INTO puanlar (user_id, kategori, puan, aciklama) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$kullanici_id, $kategori, $hesaplanan_puan, $aciklama ?: null]);
            $form_basari = "Puanınız hesaplandı ve kaydedildi: <strong>$hesaplanan_puan / 100</strong>";
        }
    }
}

$stmt = $pdo->prepare('SELECT * FROM puanlar WHERE user_id = ? ORDER BY eklenme_tarihi DESC');
$stmt->execute([$kullanici_id]);
$puanlar = $stmt->fetchAll();

$stmt = $pdo->prepare(
    'SELECT COUNT(*) as toplam,
            ROUND(AVG(puan),1) as ortalama,
            MAX(puan) as en_yuksek,
            MIN(puan) as en_dusuk
     FROM puanlar WHERE user_id = ?'
);
$stmt->execute([$kullanici_id]);
$istat = $stmt->fetch();

$toplam_kayit = (int)$istat['toplam'];
$ortalama     = $istat['ortalama'] ?? '—';
$en_yuksek    = $istat['en_yuksek'] ?? '—';
$en_dusuk     = $istat['en_dusuk'] ?? '—';
$son_puan     = !empty($puanlar) ? $puanlar[0]['puan'] : '—';

$stmt = $pdo->prepare(
    'SELECT kategori, COUNT(*) as adet, ROUND(AVG(puan),1) as ort
     FROM puanlar WHERE user_id = ? GROUP BY kategori'
);
$stmt->execute([$kullanici_id]);
$kategori_istat = $stmt->fetchAll(PDO::FETCH_ASSOC);
$kategori_map   = [];
foreach ($kategori_istat as $ki) {
    $kategori_map[$ki['kategori']] = $ki;
}

$en_zayif_kat   = null;
$en_guclu_kat   = null;
$en_zayif_ort   = 101;
$en_guclu_ort   = -1;
foreach ($kategori_map as $kat => $ki) {
    if ((float)$ki['ort'] < $en_zayif_ort) {
        $en_zayif_ort = (float)$ki['ort'];
        $en_zayif_kat = $kat;
    }
    if ((float)$ki['ort'] > $en_guclu_ort) {
        $en_guclu_ort = (float)$ki['ort'];
        $en_guclu_kat = $kat;
    }
}

$seviye        = '';
$seviye_renk   = '';
$oneri_mesaji  = '';
if ($toplam_kayit > 0 && is_numeric($ortalama)) {
    if ((float)$ortalama >= 85) {
        $seviye      = '🏆 Elit Sporcu';
        $seviye_renk = 'yesil';
        $oneri_mesaji = 'Mükemmel bir performans sergiliyorsun! Hedeflerini bir üst seviyeye taşıma zamanı.';
    } elseif ((float)$ortalama >= 70) {
        $seviye      = '💪 Gelişmekte';
        $seviye_renk = 'mavi';
        $oneri_mesaji = 'İyi gidiyorsun! ' . ($en_zayif_kat ? "$en_zayif_kat kategorisine" : 'zayıf alanlarına') . ' daha fazla odaklanarak bir üst seviyeye ulaşabilirsin.';
    } elseif ((float)$ortalama >= 50) {
        $seviye      = '📈 Başlangıç';
        $seviye_renk = 'turuncu';
        $oneri_mesaji = 'Doğru yoldasın! ' . ($en_zayif_kat ? "$en_zayif_kat alanında" : 'Düzenli çalışarak') . ' daha fazla çalışarak hızlıca gelişebilirsin.';
    } else {
        $seviye      = '🌱 Yeni Başlayan';
        $seviye_renk = 'kirmizi';
        $oneri_mesaji = 'Her büyük yolculuk küçük adımlarla başlar. Düzenli antrenman ve beslenmeye önem ver.';
    }
}

$radar_etiketler = [];
$radar_veriler   = [];
foreach ($kategoriler as $kat) {
    $radar_etiketler[] = $kat;
    $radar_veriler[]   = isset($kategori_map[$kat]) ? (float)$kategori_map[$kat]['ort'] : 0;
}

$motivasyon = '';
$motivasyon_renk = '';
if ($toplam_kayit > 0 && is_numeric($ortalama)) {
    if ((float)$ortalama >= 80) {
        $motivasyon = '🏆 Harika gidiyorsun! Hedeflerine çok yakınsın.';
        $motivasyon_renk = 'basari';
    } elseif ((float)$ortalama >= 55) {
        $motivasyon = '💪 İyi bir tempoda devam ediyorsun, biraz daha çalış!';
        $motivasyon_renk = 'bilgi';
    } else {
        $motivasyon = '📈 Gelişim için daha fazla çaba göster, her adım sayılır!';
        $motivasyon_renk = 'uyari';
    }
}

$sayfa_basligi        = 'Üye Paneli';
$css_yolu             = 'assets/css/style.css';
$ana_sayfa_yolu       = 'index.php';
$kullanici_panel_yolu = 'kullanici_panel.php';
$cikis_yolu           = 'logout.php';
require_once 'includes/header.php';
?>

<section class="py-4">
  <div class="container">

    <div class="mb-4">
      <h1>👋 Hoş geldin, <?= $ad_soyad ?>!</h1>
      <p class="text-muted">Haftalık performans değerlendirmeni yap — puan otomatik hesaplanır ve üyelik geçmişine eklenir.</p>
    </div>

    <?php if (!empty($motivasyon)): ?>
    <?php
    $alert_cls = 'alert-info';
    if ($motivasyon_renk === 'basari') $alert_cls = 'alert-success';
    elseif ($motivasyon_renk === 'uyari') $alert_cls = 'alert-warning';
    ?>
    <div class="alert <?= $alert_cls ?> mb-4"><?= $motivasyon ?></div>
    <?php endif; ?>

    <!-- İstatistik Kartları -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-4 col-lg">
        <div class="istat-kart mavi">
          <div class="istat-ikon">📋</div>
          <div class="istat-sayi"><?= $toplam_kayit ?></div>
          <div class="istat-etiket">Toplam Kayıt</div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg">
        <div class="istat-kart yesil">
          <div class="istat-ikon">⭐</div>
          <div class="istat-sayi"><?= $ortalama ?></div>
          <div class="istat-etiket">Genel Ortalama</div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg">
        <div class="istat-kart turuncu">
          <div class="istat-ikon">🏆</div>
          <div class="istat-sayi"><?= $en_yuksek ?></div>
          <div class="istat-etiket">En Yüksek Puan</div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg">
        <div class="istat-kart kirmizi">
          <div class="istat-ikon">📉</div>
          <div class="istat-sayi"><?= $en_dusuk ?></div>
          <div class="istat-etiket">En Düşük Puan</div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg">
        <div class="istat-kart mor">
          <div class="istat-ikon">🎯</div>
          <div class="istat-sayi"><?= $son_puan ?></div>
          <div class="istat-etiket">Son Puan</div>
        </div>
      </div>
    </div>

    <!-- Gelişim Seviyesi + Öneri -->
    <?php if (!empty($seviye)): ?>
    <div class="kart mb-4" style="border-left: 4px solid var(--renk-vurgu);">
      <div class="d-flex align-items-center gap-3 flex-wrap">
        <div>
          <div class="small text-muted text-uppercase mb-1" style="letter-spacing:1px;">Gelişim Seviyesi</div>
          <span class="istat-kart-rozet rozet-seviye rozet-seviye-<?= $seviye_renk ?>"><?= $seviye ?></span>
        </div>
        <div class="flex-fill border-start ps-3">
          <div class="small text-muted text-uppercase mb-1" style="letter-spacing:1px;">Kişisel Gelişim Önerisi</div>
          <p class="mb-0 small"><?= htmlspecialchars($oneri_mesaji) ?></p>
        </div>
        <?php if ($en_zayif_kat): ?>
        <div class="text-center">
          <div class="small text-muted text-uppercase mb-1" style="letter-spacing:1px;">En Zayıf Alan</div>
          <span class="rozet rozet-kategori rozet-<?= kategoriClass($en_zayif_kat) ?>"><?= htmlspecialchars($en_zayif_kat) ?></span>
          <div class="small mt-1" style="color:var(--renk-hata);"><?= $en_zayif_ort ?> / 100</div>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Profil + Radar -->
    <div class="row g-4 mb-4 align-items-start">

      <div class="col-md-5">
        <div class="kart h-100">
          <div class="kart-baslik">🏅 Üyelik Bilgilerim</div>
          <div class="bilgi-satiri">
            <span class="bilgi-etiket">Ad Soyad</span>
            <span class="bilgi-deger"><?= $ad_soyad ?></span>
          </div>
          <div class="bilgi-satiri">
            <span class="bilgi-etiket">Kullanıcı Adı</span>
            <span class="bilgi-deger">@<?= $kullanici_adi ?></span>
          </div>
          <div class="bilgi-satiri">
            <span class="bilgi-etiket">E-posta</span>
            <span class="bilgi-deger"><?= $email ?></span>
          </div>
          <div class="bilgi-satiri">
            <span class="bilgi-etiket">Üyelik Türü</span>
            <span class="bilgi-deger"><span class="rozet rozet-user">Standart Üye</span></span>
          </div>
          <div class="bilgi-satiri">
            <span class="bilgi-etiket">Üyelik Başlangıcı</span>
            <span class="bilgi-deger"><?= $kayit_tarihi_fmt ?: '—' ?></span>
          </div>

          <?php if (!empty($kategori_map)): ?>
          <div class="mt-3 pt-3 border-top">
            <div class="small fw-semibold text-muted mb-3">📊 Kategori Ortalamaları</div>
            <?php foreach ($kategoriler as $kat): ?>
              <?php if (isset($kategori_map[$kat])): ?>
              <div class="mb-2">
                <div class="d-flex justify-content-between small mb-1">
                  <span class="rozet rozet-kategori rozet-<?= kategoriClass($kat) ?>"><?= $kat ?></span>
                  <span class="text-muted"><?= $kategori_map[$kat]['ort'] ?> ort · <?= $kategori_map[$kat]['adet'] ?> kayıt</span>
                </div>
                <div class="progress-bar-bg">
                  <div class="progress-bar-dolgu <?= kategoriClass($kat) ?>-bg"
                       style="width: <?= $kategori_map[$kat]['ort'] ?>%"></div>
                </div>
              </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <?php if ($toplam_kayit > 0): ?>
          <div class="mt-3 pt-3 border-top">
            <a href="rapor.php" class="btn btn-success w-100" id="rapor-btn">📄 Performans Raporu Oluştur</a>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="col-md-7">
        <div class="kart h-100">
          <div class="kart-baslik">🎯 Performans Radar Grafiği</div>
          <?php if (!empty($kategori_map)): ?>
          <p class="small text-muted mb-3">Kategori bazlı ortalama puanlarının görsel analizi.</p>
          <div class="chart-kapsam">
            <canvas id="radarGrafik"></canvas>
          </div>
          <?php else: ?>
          <p class="bos-mesaj">📊 Grafik için en az bir değerlendirme yapmalısın.</p>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <!-- Değerlendirme Formu -->
    <div class="kart mb-4">
      <div class="kart-baslik">📝 Haftalık Performans Değerlendirmesi</div>

      <?php if (!empty($form_hata)): ?>
      <div class="alert alert-danger"><?= $form_hata ?></div>
      <?php endif; ?>
      <?php if (!empty($form_basari)): ?>
      <div class="alert alert-success"><?= $form_basari ?></div>
      <?php endif; ?>

      <p class="small text-muted mb-3">Bir kategori seç ve soruları yanıtla — üyelik puanın otomatik hesaplanır ve kaydedilir.</p>

      <form method="POST" action="kullanici_panel.php" id="degerlendirme-form" novalidate>
        <input type="hidden" name="puan_ekle" value="1">

        <div class="mb-3">
          <label for="kategori" class="form-label">Kategori Seç</label>
          <select id="kategori" name="kategori" class="form-select" onchange="kategoriDegisti(this.value)">
            <option value="">-- Kategori seçin --</option>
            <?php foreach ($kategoriler as $k): ?>
            <option value="<?= $k ?>"><?= $k ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <?php foreach ($sorular as $kat => $soru_listesi): ?>
        <div class="soru-grubu" id="sorular-<?= $kat ?>" style="display:none;">
          <?php foreach ($soru_listesi as $si => $soru): ?>
          <div class="mb-3 soru-blok">
            <label class="form-label"><?= ($si + 1) ?>. <?= htmlspecialchars($soru['soru']) ?></label>
            <div class="secenekler">
              <?php foreach ($soru['secenekler'] as $ci => $secenek): ?>
              <label class="secenek-satiri">
                <input type="radio" name="soru_<?= $si ?>" value="<?= $ci ?>">
                <span><?= htmlspecialchars($secenek['metin']) ?></span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endforeach; ?>

        <div class="mb-3">
          <label for="aciklama" class="form-label">Not <small>(isteğe bağlı)</small></label>
          <input type="text" id="aciklama" name="aciklama" class="form-control"
                 placeholder="Kısa bir not ekleyebilirsiniz">
        </div>

        <button type="submit" class="btn btn-success w-100" id="kaydet-btn" style="display:none;">
          Değerlendirmeyi Kaydet
        </button>
      </form>
    </div>

    <!-- Puan Geçmişi -->
    <div class="kart">
      <div class="kart-baslik">📋 Üyelik Performans Geçmişim</div>
      <?php if (empty($puanlar)): ?>
      <p class="bos-mesaj">🏋️ Henüz performans değerlendirmesi yapılmadı. Yukarıdaki formu kullanarak ilk üyelik puanını oluştur!</p>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover uye-tablosu">
          <thead>
            <tr>
              <th>ID</th>
              <th>Kategori</th>
              <th>Puan</th>
              <th>Not</th>
              <th>Tarih</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($puanlar as $kayit): ?>
            <tr>
              <td class="id-sutun">#<?= $kayit['id'] ?></td>
              <td>
                <span class="rozet rozet-kategori rozet-<?= kategoriClass($kayit['kategori']) ?>">
                  <?= htmlspecialchars($kayit['kategori']) ?>
                </span>
              </td>
              <td>
                <span class="puan-goster <?= (int)$kayit['puan'] >= 75 ? 'puan-iyi' : ((int)$kayit['puan'] >= 50 ? 'puan-orta' : 'puan-dusuk') ?>">
                  <?= $kayit['puan'] ?>
                </span>
                <span class="text-muted small"> / 100</span>
              </td>
              <td class="text-muted small"><?= htmlspecialchars($kayit['aciklama'] ?? '—') ?></td>
              <td class="text-muted small"><?= (new DateTime($kayit['eklenme_tarihi']))->format('d.m.Y H:i') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>

  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
function kategoriDegisti(kat) {
    document.querySelectorAll('.soru-grubu').forEach(function(el) {
        el.style.display = 'none';
    });
    document.querySelectorAll('.soru-grubu input[type="radio"]').forEach(function(r) {
        r.checked = false;
    });

    var btn = document.getElementById('kaydet-btn');
    if (kat && document.getElementById('sorular-' + kat)) {
        document.getElementById('sorular-' + kat).style.display = 'block';
        btn.style.display = 'block';
    } else {
        btn.style.display = 'none';
    }
}

<?php if (!empty($kategori_map)): ?>
(function() {
    var ctx = document.getElementById('radarGrafik');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: <?= json_encode($radar_etiketler) ?>,
            datasets: [{
                label: 'Ortalama Puan',
                data: <?= json_encode($radar_veriler) ?>,
                backgroundColor: 'rgba(34, 197, 94, 0.15)',
                borderColor: '#22c55e',
                borderWidth: 2,
                pointBackgroundColor: '#22c55e',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: '#22c55e',
                pointRadius: 5,
            }]
        },
        options: {
            responsive: true,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        stepSize: 25,
                        color: '#94a3b8',
                        backdropColor: 'transparent',
                        font: { size: 10 }
                    },
                    grid: { color: '#2d3748' },
                    angleLines: { color: '#2d3748' },
                    pointLabels: {
                        color: '#e2e8f0',
                        font: { size: 13, weight: 'bold' }
                    }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
})();
<?php endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>
