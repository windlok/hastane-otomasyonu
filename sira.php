<?php
include 'header.php';
if (!isset($_SESSION['kullanici_tc'])) {
    header('location:index.php');
    exit;
}

$rol = kullanici_rol();
$bugun = date('Y-m-d');

try {
    if ($rol === 'hasta') {
        $k_id = $db->prepare('SELECT kullanici_id FROM kullanici WHERE kullanici_tc = ?');
        $k_id->execute([$_SESSION['kullanici_tc']]);
        $ku = $k_id->fetch(PDO::FETCH_ASSOC);

        $sorgu = $db->prepare("SELECT r.*, k.kullanici_adsoyad AS doktor_ad FROM randevu r
            JOIN doktor d ON r.doktor_id = d.doktor_id
            JOIN kullanici k ON d.kullanici_id = k.kullanici_id
            WHERE r.kullanici_id = ? AND r.randevu_tarih = ? AND r.durum = 'aktif'
            ORDER BY r.randevu_saat ASC LIMIT 1");
        $sorgu->execute([$ku['kullanici_id'], $bugun]);
        $bugun_randevu = $sorgu->fetch(PDO::FETCH_ASSOC);

        if ($bugun_randevu) {
            // Aynı doktor için bugün kaç randevu var
            $toplam = $db->prepare("SELECT COUNT(*) FROM randevu WHERE doktor_id = ? AND randevu_tarih = ? AND durum = 'aktif'");
            $toplam->execute([$bugun_randevu['doktor_id'], $bugun]);
            $toplam_bugun = $toplam->fetchColumn();

            // Geçmiş saatler
            $onceki = $db->prepare("SELECT COUNT(*) FROM randevu WHERE doktor_id = ? AND randevu_tarih = ? AND randevu_saat < ? AND durum = 'aktif'");
            $onceki->execute([$bugun_randevu['doktor_id'], $bugun, $bugun_randevu['randevu_saat']]);
            $sira_once = $onceki->fetchColumn();

            $sira_no = $sira_once + 1;
        }
    } elseif ($rol === 'doktor') {
        $doktor_id = $_SESSION['doktor_id'] ?? 0;
        $sorgu = $db->prepare("SELECT r.*, k.kullanici_adsoyad FROM randevu r
            JOIN kullanici k ON r.kullanici_id = k.kullanici_id
            WHERE r.doktor_id = ? AND r.randevu_tarih = ? AND r.durum = 'aktif'
            ORDER BY r.randevu_saat ASC");
        $sorgu->execute([$doktor_id, $bugun]);
        $bugun_randevular = $sorgu->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Admin: tüm doktorlar için kuyruk
        $doktorlar = $db->query("SELECT d.doktor_id, k.kullanici_adsoyad FROM doktor d JOIN kullanici k ON d.kullanici_id = k.kullanici_id WHERE k.aktif = 1 ORDER BY k.kullanici_adsoyad")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $bugun_randevu = null;
    $bugun_randevular = [];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <meta http-equiv="refresh" content="30">
    <title><?php echo __('site_title'); ?> - <?php echo __('baslik_sira'); ?></title>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="page-container">
        <div class="page-header">
            <h2>🔢 <?php echo __('baslik_sira'); ?></h2>
            <p><?php echo __('sira_takip_aciklama'); ?></p>
        </div>

        <?php if ($rol === 'hasta'): ?>
        <?php if ($bugun_randevu): ?>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px; max-width: 600px; margin: 0 auto;">
            <div class="card" style="text-align:center; padding: 40px 20px;">
                <div style="font-size:48px; color:var(--primary); margin-bottom:10px;">🔢</div>
                <div style="font-size:14px; color:var(--text-muted); margin-bottom:5px;"><?php echo __('sira_numarasi'); ?></div>
                <div style="font-size:64px; font-weight:800; color:var(--primary);"><?php echo $sira_no; ?></div>
            </div>
            <div class="card" style="text-align:center; padding: 40px 20px;">
                <div style="font-size:14px; color:var(--text-muted); margin-bottom:5px;"><?php echo __('onceki_hasta'); ?></div>
                <div style="font-size:48px; font-weight:800; color:var(--accent);"><?php echo $sira_no - 1; ?></div>
                <div style="font-size:13px; color:var(--text-muted); margin-top:10px;"><?php echo __('doktor'); ?>: <strong><?php echo htmlspecialchars($bugun_randevu['doktor_ad']); ?></strong></div>
                <div style="font-size:13px; color:var(--text-muted);"><?php echo __('randevu_saati'); ?>: <?php echo date('H:i', strtotime($bugun_randevu['randevu_saat'])); ?></div>
            </div>
        </div>
        <?php else: ?>
        <div class="card" style="text-align:center; padding:40px;">
            <p style="font-size:16px; color:var(--text-muted);"><?php echo __('bugun_randevu_yok'); ?></p>
        </div>
        <?php endif; ?>

        <?php elseif ($rol === 'doktor'): ?>
        <div class="card">
            <h3 class="card-title"><span class="card-icon">🔢</span> <?php echo __('bugunku_sira_listesi'); ?></h3>
            <div class="table-container">
                <table>
                    <thead><tr><th><?php echo __('sira_no'); ?></th><th><?php echo __('hasta'); ?></th><th><?php echo __('saat'); ?></th><th><?php echo __('durum'); ?></th></tr></thead>
                    <tbody>
                        <?php if (empty($bugun_randevular)): ?>
                        <tr><td colspan="4" class="empty-state"><?php echo __('bugun_randevu_yok'); ?></td></tr>
                        <?php else: ?>
                        <?php $i = 1; foreach ($bugun_randevular as $r): ?>
                        <tr>
                            <td style="font-size:20px;font-weight:800;color:var(--primary);"><?php echo $i++; ?></td>
                            <td><strong><?php echo htmlspecialchars($r['kullanici_adsoyad']); ?></strong></td>
                            <td><?php echo date('H:i', strtotime($r['randevu_saat'])); ?></td>
                            <td>
                                <?php if (strtotime($r['randevu_tarih'] . ' ' . $r['randevu_saat']) < time()): ?>
                                <span style="color:#e74c3c;font-weight:600;"><?php echo __('gecti'); ?></span>
                                <?php elseif (strtotime($r['randevu_tarih'] . ' ' . $r['randevu_saat']) < time() + 1800): ?>
                                <span style="color:var(--accent);font-weight:600;"><?php echo __('siradaki'); ?></span>
                                <?php else: ?>
                                <span style="color:var(--success);font-weight:600;"><?php echo __('bekliyor'); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        <div style="text-align:center; margin-top: 20px;">
            <a href="sira_sorgu.php" style="color:var(--primary); font-size:13px;">🔍 <?php echo __('sira_sorgula'); ?></a>
        </div>
    </div>
</body>
</html>
