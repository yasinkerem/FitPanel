<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');

require_once 'includes/auth.php';
require_once 'config.php';

adminGerekli();

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

$toplam_kullanici = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$admin_sayisi     = $pdo->query("SELECT COUNT(*) FROM users WHERE rol = 'admin'")->fetchColumn();
$uye_sayisi       = $pdo->query("SELECT COUNT(*) FROM users WHERE rol = 'user'")->fetchColumn();
$toplam_puan      = $pdo->query('SELECT COUNT(*) FROM puanlar')->fetchColumn();

$ort_sorgu = $pdo->query('SELECT ROUND(AVG(puan), 1) FROM puanlar')->fetchColumn();
$genel_ortalama = $ort_sorgu ?? '—';

$kategori_analiz = $pdo->query(
    'SELECT kategori,
            COUNT(*) as adet,
            ROUND(AVG(puan), 1) as ort,
            MAX(puan) as en_yuksek,
            MIN(puan) as en_dusuk
     FROM puanlar GROUP BY kategori ORDER BY ort DESC'
)->fetchAll();

$en_zayif_kat   = null;
$en_zayif_ort   = 101;
foreach ($kategori_analiz as $ka) {
    if ((float)$ka['ort'] < $en_zayif_ort) {
        $en_zayif_ort = (float)$ka['ort'];
        $en_zayif_kat = $ka;
    }
}

$zayif_mesaj = '';
if ($en_zayif_kat) {
    $ort = (float)$en_zayif_kat['ort'];
    if ($ort < 40) {
        $zayif_mesaj = 'Üyeler bu kategoride ciddi güçlük yaşıyor. Özel antrenman programı veya rehberlik önerilir.';
    } elseif ($ort < 60) {
        $zayif_mesaj = 'Bu kategori gelişim alanı olarak öne çıkıyor. Grup etkinlikleri düzenlenebilir.';
    } else {
        $zayif_mesaj = 'Üyeler genelde iyi gidiyor, ancak bu kategoride küçük iyileştirmeler faydalı olabilir.';
    }
}

$uyeler = $pdo->query(
    'SELECT id, ad_soyad, kullanici_adi, email, rol, kayit_tarihi
     FROM users ORDER BY kayit_tarihi DESC'
)->fetchAll();

$leaderboard = $pdo->query(
    'SELECT u.id, u.ad_soyad, u.kullanici_adi,
            COUNT(p.id) as kayit_sayisi,
            ROUND(AVG(p.puan), 1) as ort_puan
     FROM users u
     INNER JOIN puanlar p ON u.id = p.user_id
     WHERE u.rol = \'user\'
     GROUP BY u.id, u.ad_soyad, u.kullanici_adi
     ORDER BY ort_puan DESC
     LIMIT 10'
)->fetchAll();

$puan_kayitlari = $pdo->query(
    'SELECT p.id, p.kategori, p.puan, p.aciklama, p.eklenme_tarihi,
            u.ad_soyad, u.kullanici_adi
     FROM puanlar p
     INNER JOIN users u ON p.user_id = u.id
     ORDER BY p.eklenme_tarihi DESC'
)->fetchAll();

$bar_etiketler = array_column($kategori_analiz, 'kategori');
$bar_veriler   = array_column($kategori_analiz, 'ort');

$sayfa_basligi  = 'Yönetim Paneli';
$css_yolu       = 'assets/css/style.css';
$ana_sayfa_yolu = 'index.php';
$cikis_yolu     = 'logout.php';
require_once 'includes/header.php';
?>

<section class="py-4">
    <div class="container">

        <h1 class="mb-1">⚙️ Yönetim Paneli</h1>
        <p class="text-muted mb-4">Hoş geldin, <?= htmlspecialchars($_SESSION['ad_soyad']) ?>. Spor salonu üye verileri ve performans kayıtları aşağıda listeleniyor.</p>

        <!-- İstatistik Kartları -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl">
                <div class="istat-kart mavi h-100">
                    <div class="istat-ikon">👥</div>
                    <div class="istat-sayi"><?= $toplam_kullanici ?></div>
                    <div class="istat-etiket">Toplam Üye</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="istat-kart turuncu h-100">
                    <div class="istat-ikon">⚙️</div>
                    <div class="istat-sayi"><?= $admin_sayisi ?></div>
                    <div class="istat-etiket">Yönetici Sayısı</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="istat-kart yesil h-100">
                    <div class="istat-ikon">🏃</div>
                    <div class="istat-sayi"><?= $uye_sayisi ?></div>
                    <div class="istat-etiket">Aktif Üye</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="istat-kart mor h-100">
                    <div class="istat-ikon">📊</div>
                    <div class="istat-sayi"><?= $toplam_puan ?></div>
                    <div class="istat-etiket">Toplam Değerlendirme</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="istat-kart kirmizi h-100">
                    <div class="istat-ikon">⭐</div>
                    <div class="istat-sayi"><?= $genel_ortalama ?></div>
                    <div class="istat-etiket">Genel Ort. Puan</div>
                </div>
            </div>
        </div>

        <?php if (!empty($kategori_analiz)): ?>
        <div class="row g-3 mb-4">
            <div class="col-md-7">
                <div class="kart h-100">
                    <div class="kart-baslik">📊 Kategori Performans Grafiği</div>
                    <p style="font-size:0.82rem; color:var(--renk-metin-soluk); margin-bottom:12px;">
                        Tüm üyelerin kategori bazlı ortalama puanları.
                    </p>
                    <div class="chart-kapsam">
                        <canvas id="barGrafik"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="kart h-100">
                    <div class="kart-baslik">🔍 Genel Zayıf Alan Analizi</div>
                    <?php if ($en_zayif_kat): ?>
                    <div class="text-center py-3">
                        <div style="font-size:0.8rem; color:var(--renk-metin-soluk); margin-bottom:8px; text-transform:uppercase; letter-spacing:1px;">En Düşük Ortalamaya Sahip Kategori</div>
                        <span class="rozet rozet-kategori rozet-<?= kategoriClass($en_zayif_kat['kategori']) ?>" style="font-size:1rem; padding:6px 18px;">
                            <?= htmlspecialchars($en_zayif_kat['kategori']) ?>
                        </span>
                        <div style="font-size:1.8rem; font-weight:900; color:var(--renk-hata); margin: 12px 0 4px;">
                            <?= $en_zayif_kat['ort'] ?> / 100
                        </div>
                        <div style="margin: 0 auto 16px; max-width:200px;">
                            <div class="progress-bar-bg">
                                <div class="progress-bar-dolgu <?= kategoriClass($en_zayif_kat['kategori']) ?>-bg"
                                     style="width:<?= $en_zayif_kat['ort'] ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning" style="margin-top:0;">
                        ⚠️ <?= htmlspecialchars($zayif_mesaj) ?>
                    </div>
                    <?php else: ?>
                    <p class="bos-mesaj">Henüz yeterli veri yok.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($kategori_analiz)): ?>
        <div class="kart mb-4">
            <div class="kart-baslik">📊 Üye Performans Analizi (Kategori Bazlı)</div>
            <div class="table-responsive">
                <table class="table table-hover uye-tablosu">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Kayıt Sayısı</th>
                            <th>Ortalama Puan</th>
                            <th>En Yüksek</th>
                            <th>En Düşük</th>
                            <th>Performans</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kategori_analiz as $ka): ?>
                        <tr>
                            <td><span class="rozet rozet-kategori rozet-<?= kategoriClass($ka['kategori']) ?>"><?= htmlspecialchars($ka['kategori']) ?></span></td>
                            <td><?= $ka['adet'] ?> kayıt</td>
                            <td><strong><?= $ka['ort'] ?></strong> / 100</td>
                            <td style="color:var(--renk-basari);"><?= $ka['en_yuksek'] ?></td>
                            <td style="color:var(--renk-hata);"><?= $ka['en_dusuk'] ?></td>
                            <td style="min-width:120px;">
                                <div class="progress-bar-bg">
                                    <div class="progress-bar-dolgu <?= kategoriClass($ka['kategori']) ?>-bg"
                                         style="width:<?= $ka['ort'] ?>%"></div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($leaderboard)): ?>
        <div class="kart mb-4">
            <div class="kart-baslik">🏆 Liderlik Tablosu (Top 10)</div>
            <ul class="list-unstyled mb-0">
                <?php foreach ($leaderboard as $i => $lb): ?>
                <?php
                    $sira_no   = $i + 1;
                    $sira_sinif = match(true) {
                        $sira_no === 1 => 'altin',
                        $sira_no === 2 => 'gumus',
                        $sira_no === 3 => 'bronz',
                        default        => 'diger',
                    };
                    $sira_emoji = match(true) {
                        $sira_no === 1 => '🥇',
                        $sira_no === 2 => '🥈',
                        $sira_no === 3 => '🥉',
                        default        => "#$sira_no",
                    };
                ?>
                <li class="leaderboard-satir">
                    <span class="lb-sira <?= $sira_sinif ?>"><?= $sira_emoji ?></span>
                    <span class="lb-isim">
                        <?= htmlspecialchars($lb['ad_soyad']) ?>
                        <span class="lb-kullanici">@<?= htmlspecialchars($lb['kullanici_adi']) ?> · <?= $lb['kayit_sayisi'] ?> değerlendirme</span>
                    </span>
                    <span class="lb-puan"><?= $lb['ort_puan'] ?></span>
                    <div class="lb-bar-wrap">
                        <div class="lb-bar" style="width:<?= $lb['ort_puan'] ?>%"></div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="kart mb-4">
            <div class="kart-baslik">👥 Kayıtlı Üyeler</div>
            <div class="table-responsive">
                <table class="table table-hover uye-tablosu">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ad Soyad</th>
                            <th>Kullanıcı Adı</th>
                            <th>E-posta</th>
                            <th>Üyelik Türü</th>
                            <th>Üyelik Başlangıcı</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($uyeler as $uye): ?>
                        <?php
                            $dt = new DateTime($uye['kayit_tarihi']);
                            $tarih_fmt = $dt->format('d.m.Y H:i');
                        ?>
                        <tr>
                            <td class="id-sutun">#<?= $uye['id'] ?></td>
                            <td><?= htmlspecialchars($uye['ad_soyad']) ?></td>
                            <td style="color: var(--renk-metin-soluk);">@<?= htmlspecialchars($uye['kullanici_adi']) ?></td>
                            <td style="color: var(--renk-metin-soluk);"><?= htmlspecialchars($uye['email']) ?></td>
                            <td>
                                <?php if ($uye['rol'] === 'admin'): ?>
                                    <span class="rozet rozet-admin">Yönetici</span>
                                <?php else: ?>
                                    <span class="rozet rozet-user">Standart Üye</span>
                                <?php endif; ?>
                            </td>
                            <td style="color: var(--renk-metin-soluk);"><?= $tarih_fmt ?></td>
                            <td>
                                <?php if ($uye['rol'] === 'user'): ?>
                                <a href="kullanici_detay.php?id=<?= (int)$uye['id'] ?>" class="btn btn-outline-secondary btn-sm">
                                    🔍 Detay Gör
                                </a>
                                <?php else: ?>
                                <span style="color:var(--renk-metin-cok-soluk); font-size:0.8rem;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="kart">
            <div class="kart-baslik">📋 Tüm Üye Performans Kayıtları</div>
            <?php if (empty($puan_kayitlari)): ?>
                <p class="bos-mesaj">Henüz hiç performans değerlendirmesi bulunmuyor.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover uye-tablosu">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kullanıcı</th>
                            <th>Kategori</th>
                            <th>Puan</th>
                            <th>Açıklama</th>
                            <th>Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($puan_kayitlari as $kayit): ?>
                        <?php
                            $dt = new DateTime($kayit['eklenme_tarihi']);
                            $tarih_fmt = $dt->format('d.m.Y H:i');
                        ?>
                        <tr>
                            <td class="id-sutun">#<?= $kayit['id'] ?></td>
                            <td>
                                <?= htmlspecialchars($kayit['ad_soyad']) ?>
                                <br><small style="color: var(--renk-metin-soluk);">@<?= htmlspecialchars($kayit['kullanici_adi']) ?></small>
                            </td>
                            <td><span class="rozet rozet-kategori rozet-<?= kategoriClass($kayit['kategori']) ?>"><?= htmlspecialchars($kayit['kategori']) ?></span></td>
                            <td><strong><?= $kayit['puan'] ?></strong> / 100</td>
                            <td style="color: var(--renk-metin-soluk);"><?= htmlspecialchars($kayit['aciklama'] ?? '—') ?></td>
                            <td style="color: var(--renk-metin-soluk);"><?= $tarih_fmt ?></td>
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
// Bar Grafiği
<?php if (!empty($kategori_analiz)): ?>
(function() {
    var ctx = document.getElementById('barGrafik');
    if (!ctx) return;
    var renkler = ['#3b82f6', '#22c55e', '#a855f7', '#f59e0b'];
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($bar_etiketler) ?>,
            datasets: [{
                label: 'Ortalama Puan',
                data: <?= json_encode($bar_veriler) ?>,
                backgroundColor: renkler.map(function(r) { return r + '33'; }),
                borderColor: renkler,
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: { color: '#94a3b8', stepSize: 25 },
                    grid: { color: '#2d3748' }
                },
                x: {
                    ticks: { color: '#e2e8f0', font: { weight: 'bold' } },
                    grid: { color: 'transparent' }
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
