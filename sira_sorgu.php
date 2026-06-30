<?php
session_start();
global $lang;
$dil = isset($_COOKIE['dil']) && in_array($_COOKIE['dil'], ['tr','en'], true) ? $_COOKIE['dil'] : 'tr';
$dil_dosyasi = __DIR__ . "/lang/$dil.php";
if (file_exists($dil_dosyasi)) {
    include $dil_dosyasi;
} else {
    include __DIR__ . "/lang/tr.php";
}
function __($key) {
    global $lang;
    return isset($lang[$key]) ? $lang[$key] : $key;
}

$hata = '';
$sonuc = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sira_sorgula'])) {
    $tc = isset($_POST['tc']) ? trim($_POST['tc']) : '';

    if (!preg_match('/^[0-9]{11}$/', $tc)) {
        $hata = __('sira_sorgu_hata_tc');
    } else {
        try {
            $db = new PDO('mysql:host=127.0.0.1;port=3306;dbname=hastane_otomasyonu;charset=utf8mb4', 'root', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $bugun = date('Y-m-d');
            $sorgu = $db->prepare("SELECT r.randevu_id, r.randevu_tarih, r.randevu_saat, r.sira_no, r.randevu_klinik,
                    r.randevu_doktoru, r.doktor_id, k.kullanici_adsoyad, k.kullanici_tc
                FROM randevu r
                JOIN kullanici k ON r.kullanici_id = k.kullanici_id
                WHERE k.kullanici_tc = ? AND r.randevu_tarih >= ? AND r.durum = 'aktif'
                ORDER BY r.randevu_tarih ASC, r.randevu_saat ASC
                LIMIT 1");
            $sorgu->execute([$tc, $bugun]);
            $randevu = $sorgu->fetch();

            if (!$randevu) {
                $hata = __('sira_sorgu_hata_bulunamadi');
            } else {
                $onceki = $db->prepare("SELECT COUNT(*) FROM randevu WHERE doktor_id = ? AND randevu_tarih = ? AND randevu_saat < ? AND durum = 'aktif'");
                $onceki->execute([$randevu['doktor_id'], $randevu['randevu_tarih'], $randevu['randevu_saat']]);
                $sira_once = $onceki->fetchColumn();

                $toplam = $db->prepare("SELECT COUNT(*) FROM randevu WHERE doktor_id = ? AND randevu_tarih = ? AND durum = 'aktif'");
                $toplam->execute([$randevu['doktor_id'], $randevu['randevu_tarih']]);
                $toplam_bugun = $toplam->fetchColumn();

                $ad_soyad = $randevu['kullanici_adsoyad'];
                $parcalar = explode(' ', $ad_soyad, 2);
                $ad = $parcalar[0];
                $soyad = $parcalar[1] ?? '';

                $anonim_ad = mb_substr($ad, 0, 1, 'UTF-8') . str_repeat('*', max(0, mb_strlen($ad, 'UTF-8') - 1));
                $anonim_soyad = $soyad ? mb_substr($soyad, 0, 1, 'UTF-8') . str_repeat('*', max(0, mb_strlen($soyad, 'UTF-8') - 1)) : '';

                $sonuc = [
                    'anonim_ad' => $anonim_ad,
                    'anonim_soyad' => $anonim_soyad,
                    'doktor' => $randevu['randevu_doktoru'],
                    'klinik' => $randevu['randevu_klinik'],
                    'saat' => $randevu['randevu_saat'] ? date('H:i', strtotime($randevu['randevu_saat'])) : '-',
                    'tarih' => date('d.m.Y', strtotime($randevu['randevu_tarih'])),
                    'sira_no' => $sira_once + 1,
                    'sira_once' => $sira_once,
                    'toplam' => $toplam_bugun,
                ];
            }
        } catch (PDOException $e) {
            $hata = __('sira_sorgu_hata_sistem');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('sira_sorgu_baslik'); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #0f1923; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .container { width: 100%; max-width: 480px; }
        .card { background: #1a2332; border-radius: 16px; padding: 35px; box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
        .logo { text-align: center; margin-bottom: 25px; }
        .logo-icon { font-size: 42px; }
        .logo h1 { color: #fff; font-size: 18px; margin-top: 8px; }
        .logo p { color: #8899aa; font-size: 13px; margin-top: 4px; }
        .form-group { margin-bottom: 16px; }
        label { display: block; color: #c0ccda; font-size: 12px; font-weight: 600; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        input { width: 100%; padding: 12px 14px; background: #0f1923; border: 1px solid #2a3a4a; border-radius: 8px; color: #fff; font-size: 16px; outline: none; transition: border 0.2s; text-align: center; letter-spacing: 4px; }
        input:focus { border-color: #4a9eff; }
        .btn { width: 100%; padding: 13px; background: #4a9eff; border: none; border-radius: 8px; color: #fff; font-size: 15px; font-weight: 700; cursor: pointer; transition: background 0.2s; margin-top: 6px; }
        .btn:hover { background: #3a7ecc; }
        .error { background: rgba(255,60,60,0.15); color: #ff6b6b; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 18px; text-align: center; border: 1px solid rgba(255,60,60,0.2); }
        .result-card { background: linear-gradient(135deg, #1a2332, #1e2d42); border-radius: 16px; padding: 30px; margin-top: 20px; border: 1px solid #2a3a4a; }
        .patient-name { text-align: center; font-size: 28px; font-weight: 800; color: #fff; letter-spacing: 2px; margin-bottom: 4px; }
        .patient-label { text-align: center; color: #8899aa; font-size: 12px; margin-bottom: 20px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px; }
        .info-item { background: #0f1923; border-radius: 10px; padding: 14px; text-align: center; }
        .info-item .label { color: #8899aa; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .info-item .value { color: #fff; font-size: 16px; font-weight: 700; }
        .info-item .value.doctor { color: #4a9eff; }
        .info-item .value.time { color: #4caf50; }
        .info-item .value.clinic { color: #ff9800; }
        .big-number { text-align: center; padding: 20px; }
        .big-number .number { font-size: 64px; font-weight: 900; color: #4a9eff; line-height: 1; }
        .big-number .number-label { color: #8899aa; font-size: 13px; margin-top: 6px; }
        .ahead { text-align: center; color: #8899aa; font-size: 14px; margin-top: 10px; }
        .ahead strong { color: #ff6b6b; font-size: 18px; }
        .footer { text-align: center; margin-top: 16px; }
        .footer a { color: #4a9eff; font-size: 13px; text-decoration: none; }
        .footer a:hover { text-decoration: underline; }
        .back-link { display: inline-block; margin-top: 16px; color: #8899aa; font-size: 13px; text-decoration: none; }
        .back-link:hover { color: #4a9eff; }
        @media (max-width: 500px) { .info-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo">
                <div class="logo-icon">🔢</div>
                <h1><?php echo __('sira_sorgu_baslik'); ?></h1>
                <p><?php echo __('sira_sorgu_aciklama'); ?></p>
            </div>

            <?php if ($hata): ?>
            <div class="error"><?php echo htmlspecialchars($hata); ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label for="tc"><?php echo __('sira_sorgu_tc_label'); ?></label>
                    <input type="text" id="tc" name="tc" placeholder="<?php echo __('sira_sorgu_tc_placeholder'); ?>" maxlength="11" required pattern="\d{11}" inputmode="numeric" autocomplete="off">
                </div>
                <button type="submit" name="sira_sorgula" class="btn"><?php echo __('sira_sorgu_button'); ?></button>
            </form>

            <div class="footer">
                <a href="index.php">← <?php echo __('sira_sorgu_ana_sayfa'); ?></a>
            </div>
        </div>

        <?php if ($sonuc): ?>
        <div class="result-card">
            <div class="patient-name"><?php echo htmlspecialchars($sonuc['anonim_ad'] . ' ' . $sonuc['anonim_soyad']); ?></div>
            <div class="patient-label"><?php echo __('sira_sorgu_hasta_label'); ?></div>

            <div class="big-number">
                <div class="number">#<?php echo $sonuc['sira_no']; ?></div>
                <div class="number-label"><?php echo __('sira_sorgu_sira_no'); ?> (<?php echo __('sira_sorgu_toplam'); ?> <?php echo $sonuc['toplam']; ?> <?php echo __('sira_sorgu_hasta'); ?>)</div>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <div class="label"><?php echo __('sira_sorgu_doktor'); ?></div>
                    <div class="value doctor"><?php echo htmlspecialchars($sonuc['doktor']); ?></div>
                </div>
                <div class="info-item">
                    <div class="label"><?php echo __('sira_sorgu_bolum'); ?></div>
                    <div class="value clinic"><?php echo htmlspecialchars($sonuc['klinik']); ?></div>
                </div>
                <div class="info-item">
                    <div class="label"><?php echo __('sira_sorgu_tarih'); ?></div>
                    <div class="value"><?php echo $sonuc['tarih']; ?></div>
                </div>
                <div class="info-item">
                    <div class="label"><?php echo __('sira_sorgu_saat'); ?></div>
                    <div class="value time"><?php echo $sonuc['saat']; ?></div>
                </div>
            </div>

            <div class="ahead">
                <?php echo __('sira_sorgu_once_on'); ?><strong><?php echo $sonuc['sira_once']; ?></strong><?php echo __('sira_sorgu_once_arka'); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
