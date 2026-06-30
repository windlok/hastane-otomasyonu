<?php
include 'header.php';
if (!isset($_SESSION['kullanici_tc'])) {
    header('location:index.php');
    exit;
}

$rol = kullanici_rol();
$baslangic = isset($_GET['baslangic']) ? $_GET['baslangic'] : date('Y-m-d', strtotime('-30 days'));
$bitis = isset($_GET['bitis']) ? $_GET['bitis'] : date('Y-m-d');

try {
    if ($rol === 'admin') {
        $sorgu = $db->prepare("SELECT r.*, k.kullanici_adsoyad, k.kullanici_tc AS hasta_tc FROM randevu r
            LEFT JOIN kullanici k ON r.kullanici_id = k.kullanici_id
            WHERE r.randevu_tarih BETWEEN ? AND ?
            ORDER BY r.randevu_tarih ASC, r.randevu_saat ASC");
        $sorgu->execute([$baslangic, $bitis]);
    } elseif ($rol === 'doktor') {
        $doktor_id = $_SESSION['doktor_id'] ?? 0;
        $sorgu = $db->prepare("SELECT r.*, k.kullanici_adsoyad, k.kullanici_tc AS hasta_tc FROM randevu r
            JOIN kullanici k ON r.kullanici_id = k.kullanici_id
            WHERE r.doktor_id = ? AND r.randevu_tarih BETWEEN ? AND ?
            ORDER BY r.randevu_tarih ASC, r.randevu_saat ASC");
        $sorgu->execute([$doktor_id, $baslangic, $bitis]);
    } else {
        $k_id = $db->prepare('SELECT kullanici_id FROM kullanici WHERE kullanici_tc = ?');
        $k_id->execute([$_SESSION['kullanici_tc']]);
        $ku = $k_id->fetch(PDO::FETCH_ASSOC);
        $sorgu = $db->prepare("SELECT * FROM randevu WHERE kullanici_id = ? AND randevu_tarih BETWEEN ? AND ? ORDER BY randevu_tarih ASC, randevu_saat ASC");
        $sorgu->execute([$ku['kullanici_id'], $baslangic, $bitis]);
    }
    $randevular = $sorgu->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $randevular = [];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('baslik_rapor'); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 30px; color: #333; }
        .report-header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #1a73e8; }
        .report-header h1 { font-size: 22px; color: #1a73e8; margin-bottom: 5px; }
        .report-header p { color: #666; font-size: 13px; }
        .report-meta { margin-bottom: 20px; font-size: 13px; color: #555; }
        .report-meta span { margin-right: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1a73e8; color: #fff; padding: 10px 12px; font-size: 13px; text-align: left; }
        td { padding: 8px 12px; border-bottom: 1px solid #e0e0e0; font-size: 12px; }
        tr:nth-child(even) { background: #f8f9fa; }
        .status-aktif { color: #2e7d32; font-weight: 600; }
        .status-iptal { color: #c62828; font-weight: 600; }
        .status-gecmis { color: #999; }
        .footer { text-align: center; margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 11px; color: #999; }
        .no-data { text-align: center; padding: 40px; color: #999; font-size: 14px; }
        .print-btn { display: inline-block; padding: 10px 24px; background: #1a73e8; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; margin-bottom: 20px; text-decoration: none; }
        .print-btn:hover { background: #1557b0; }
        @media print { .print-btn, .no-print { display: none; } body { padding: 10px; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:15px;">
        <form method="get" style="display:flex; gap:10px; align-items:end; flex-wrap:wrap;">
            <div><label style="font-size:12px;font-weight:600;"><?php echo __('bastan'); ?></label><input type="date" name="baslangic" value="<?php echo $baslangic; ?>" style="padding:6px 10px;border:1px solid #ccc;border-radius:4px;"></div>
            <div><label style="font-size:12px;font-weight:600;"><?php echo __('bitis'); ?></label><input type="date" name="bitis" value="<?php echo $bitis; ?>" style="padding:6px 10px;border:1px solid #ccc;border-radius:4px;"></div>
            <button type="submit" style="padding:6px 16px;background:#1a73e8;color:#fff;border:none;border-radius:4px;cursor:pointer;"><?php echo __('listele'); ?></button>
            <a href="javascript:window.print()" class="print-btn"><?php echo __('pdf_yazdir'); ?></a>
        </form>
    </div>

    <div class="report-header">
        <h1><?php echo __('hastane_otomasyonu_rapor'); ?></h1>
        <p><?php echo __('randevu_takip_sistemi'); ?></p>
    </div>

    <div class="report-meta">
        <span><strong><?php echo __('donem'); ?>:</strong> <?php echo date('d.m.Y', strtotime($baslangic)); ?> — <?php echo date('d.m.Y', strtotime($bitis)); ?></span>
        <span><strong><?php echo __('toplam'); ?>:</strong> <?php echo count($randevular); ?> <?php echo __('randevu'); ?></span>
        <span><strong><?php echo __('olusturan'); ?>:</strong> <?php echo htmlspecialchars($_SESSION['kullanici_adsoyad']); ?> (<?php echo ucfirst($rol); ?>)</span>
        <span><strong><?php echo __('tarih'); ?>:</strong> <?php echo date('d.m.Y H:i'); ?></span>
    </div>

    <?php if (empty($randevular)): ?>
    <div class="no-data"><?php echo __('donem_randevu_yok'); ?></div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <?php if ($rol !== 'hasta'): ?><th><?php echo __('hasta'); ?></th><?php endif; ?>
                <th><?php echo __('doktor'); ?></th>
                <th><?php echo __('klinik'); ?></th>
                <th><?php echo __('hastane'); ?></th>
                <th><?php echo __('tarih'); ?></th>
                <th><?php echo __('saat'); ?></th>
                <th><?php echo __('durum'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach ($randevular as $r):
                $r_tarih = strtotime($r['randevu_tarih']);
                $gecmis = $r_tarih < strtotime(date('Y-m-d'));
                $durum = $r['durum'] ?? 'aktif';
                if ($durum === 'iptal') {
                    $durum_cls = 'status-iptal';
                    $durum_text = __('iptal');
                } elseif ($gecmis) {
                    $durum_cls = 'status-gecmis';
                    $durum_text = __('gecti');
                } else {
                    $durum_cls = 'status-aktif';
                    $durum_text = __('aktif');
                }
            ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <?php if ($rol !== 'hasta'): ?><td><?php echo htmlspecialchars($r['kullanici_adsoyad'] ?? __('silinmis')); ?></td><?php endif; ?>
                <td><?php echo htmlspecialchars($r['randevu_doktoru']); ?></td>
                <td><?php echo htmlspecialchars($r['randevu_klinik']); ?></td>
                <td><?php echo htmlspecialchars($r['randevu_hastane']); ?></td>
                <td><?php echo date('d.m.Y', $r_tarih); ?></td>
                <td><?php echo $r['randevu_saat'] ? date('H:i', strtotime($r['randevu_saat'])) : '-'; ?></td>
                <td class="<?php echo $durum_cls; ?>"><?php echo $durum_text; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <div class="footer">
        <?php echo __('site_title'); ?> &mdash; <?php echo date('Y'); ?> &mdash; <?php echo __('rapor_footer'); ?>
    </div>
</body>
</html>
