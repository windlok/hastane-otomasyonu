<?php
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

try {
    $db = new PDO('mysql:host=127.0.0.1;port=3306;dbname=hastane_otomasyonu;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $bugun = date('Y-m-d');

    $doktorlar = $db->query("SELECT d.doktor_id, k.kullanici_adsoyad, d.klinik, d.hastane
        FROM doktor d JOIN kullanici k ON d.kullanici_id = k.kullanici_id WHERE k.aktif = 1 ORDER BY d.klinik, k.kullanici_adsoyad")
        ->fetchAll(PDO::FETCH_ASSOC);

    $tum_randevular = [];
    if (!empty($doktorlar)) {
        $ids = array_column($doktorlar, 'doktor_id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sorgu = $db->prepare("SELECT r.*, k.kullanici_adsoyad,
                TIME_FORMAT(r.randevu_saat, '%H:%i') AS saat_str
            FROM randevu r
            JOIN kullanici k ON r.kullanici_id = k.kullanici_id
            WHERE r.doktor_id IN ($placeholders) AND r.randevu_tarih = ? AND r.durum = 'aktif'
            ORDER BY r.doktor_id, r.randevu_saat ASC");
        $params = $ids;
        $params[] = $bugun;
        $sorgu->execute($params);
        while ($row = $sorgu->fetch()) {
            $tum_randevular[$row['doktor_id']][] = $row;
        }
    }
} catch (PDOException $e) {
    $doktorlar = [];
    $tum_randevular = [];
}

function anonim_ad($adsoyad) {
    $p = explode(' ', $adsoyad, 2);
    $ad = $p[0];
    $soyad = $p[1] ?? '';
    $a = mb_substr($ad, 0, 1, 'UTF-8') . str_repeat('*', max(0, mb_strlen($ad, 'UTF-8') - 1));
    $s = $soyad ? mb_substr($soyad, 0, 1, 'UTF-8') . str_repeat('*', max(0, mb_strlen($soyad, 'UTF-8') - 1)) : '';
    return "$a $s";
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="15">
    <title><?php echo __('sira_ekrani_baslik'); ?> - <?php echo __('site_title'); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #0a0e14; color: #fff; min-height: 100vh; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { font-size: 28px; color: #4a9eff; letter-spacing: 1px; }
        .header p { color: #8899aa; font-size: 14px; margin-top: 4px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 16px; }
        .doctor-card { background: #121a26; border-radius: 12px; padding: 18px; border: 1px solid #1e2a3a; }
        .doctor-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #1e2a3a; }
        .doctor-name { font-size: 16px; font-weight: 700; color: #4a9eff; }
        .doctor-clinic { font-size: 12px; color: #ff9800; background: rgba(255,152,0,0.15); padding: 2px 10px; border-radius: 10px; }
        .queue-item { display: flex; align-items: center; padding: 8px 0; border-bottom: 1px solid #0f1722; }
        .queue-item:last-child { border-bottom: none; }
        .q-sira { width: 40px; font-size: 20px; font-weight: 900; color: #4a9eff; text-align: center; }
        .q-name { flex: 1; font-size: 15px; color: #e0e8f0; letter-spacing: 0.5px; }
        .q-time { width: 60px; text-align: right; font-size: 14px; color: #4caf50; font-weight: 600; }
        .q-status { width: 70px; text-align: right; font-size: 12px; }
        .status-bekliyor { color: #8899aa; }
        .status-siradaki { color: #ff9800; font-weight: 700; }
        .status-gecti { color: #e74c3c; }
        .empty { text-align: center; padding: 40px; color: #556; }
        .empty h2 { font-size: 48px; margin-bottom: 10px; }
        .footer { text-align: center; margin-top: 30px; padding-top: 16px; border-top: 1px solid #1e2a3a; color: #556; font-size: 12px; }
        .refresh-note { color: #445; font-size: 11px; text-align: center; margin-top: 8px; }
        .no-patients { color: #445; font-size: 13px; text-align: center; padding: 20px 0; }
        @media (max-width: 800px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏥 <?php echo __('sira_ekrani_baslik'); ?></h1>
        <p><?php echo date('d.m.Y l'); ?> — <?php echo __('sira_ekrani_aciklama'); ?></p>
    </div>

    <?php if (empty($doktorlar)): ?>
    <div class="empty">
        <h2>📋</h2>
        <p><?php echo __('sira_ekrani_bugun_yok'); ?></p>
    </div>
    <?php else: ?>
    <div class="grid">
        <?php foreach ($doktorlar as $d):
            $randevular = $tum_randevular[$d['doktor_id']] ?? [];
        ?>
        <div class="doctor-card">
            <div class="doctor-header">
                <span class="doctor-name"><?php echo htmlspecialchars($d['kullanici_adsoyad']); ?></span>
                <span class="doctor-clinic"><?php echo htmlspecialchars($d['klinik']); ?></span>
            </div>
            <?php if (empty($randevular)): ?>
            <div class="no-patients"><?php echo __('sira_ekrani_hasta_yok'); ?></div>
            <?php else: ?>
            <?php $sira = 1; foreach ($randevular as $r):
                $gecmis = strtotime($r['randevu_tarih'] . ' ' . $r['randevu_saat']) < time();
                $siradaki = !$gecmis && $sira <= 1;
            ?>
            <div class="queue-item">
                <div class="q-sira"><?php echo $sira++; ?></div>
                <div class="q-name"><?php echo htmlspecialchars(anonim_ad($r['kullanici_adsoyad'])); ?></div>
                <div class="q-time"><?php echo $r['saat_str']; ?></div>
                <div class="q-status <?php echo $gecmis ? 'status-gecti' : ($siradaki ? 'status-siradaki' : 'status-bekliyor'); ?>">
                    <?php echo $gecmis ? __('sira_ekrani_gecti') : ($siradaki ? __('sira_ekrani_sirada') : ''); ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="footer">
        <?php echo __('sira_ekrani_footer'); ?> &mdash; <?php echo date('Y'); ?>
        <div class="refresh-note">🔃 <?php echo __('sira_ekrani_refresh'); ?></div>
    </div>
</body>
</html>
