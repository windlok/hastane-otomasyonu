<?php
include 'header.php';
if (!isset($_SESSION['kullanici_tc'])) {
    header('location:index.php');
    exit;
}

$rol = kullanici_rol();
$recete_id = isset($_GET['recete_id']) ? intval($_GET['recete_id']) : 0;

if (!$recete_id) {
    header('location:' . ($rol === 'doktor' ? 'doktor_panel.php' : 'randevu.php'));
    exit;
}

try {
    if ($rol === 'doktor') {
        $doktor_id = $_SESSION['doktor_id'] ?? 0;
        $rs = $db->prepare("SELECT r.*, k.kullanici_adsoyad AS hasta_ad, k.kullanici_tc AS hasta_tc, d2.kullanici_adsoyad AS doktor_ad
            FROM recete r
            JOIN kullanici k ON r.hasta_id = k.kullanici_id
            JOIN doktor d ON r.doktor_id = d.doktor_id
            JOIN kullanici d2 ON d.kullanici_id = d2.kullanici_id
            WHERE r.recete_id = ? AND r.doktor_id = ?");
        $rs->execute([$recete_id, $doktor_id]);
    } else {
        $k_id = $db->prepare('SELECT kullanici_id FROM kullanici WHERE kullanici_tc = ?');
        $k_id->execute([$_SESSION['kullanici_tc']]);
        $ku = $k_id->fetch(PDO::FETCH_ASSOC);
        $rs = $db->prepare("SELECT r.*, k.kullanici_adsoyad AS hasta_ad, k.kullanici_tc AS hasta_tc, d2.kullanici_adsoyad AS doktor_ad
            FROM recete r
            JOIN kullanici k ON r.hasta_id = k.kullanici_id
            JOIN doktor d ON r.doktor_id = d.doktor_id
            JOIN kullanici d2 ON d.kullanici_id = d2.kullanici_id
            WHERE r.recete_id = ? AND r.hasta_id = ?");
        $rs->execute([$recete_id, $ku['kullanici_id']]);
    }
    $recete = $rs->fetch(PDO::FETCH_ASSOC);
    if (!$recete) {
        header('location:index.php');
        exit;
    }

    $ilaclar = explode("\n", trim($recete['ilaclar']));
} catch (PDOException $e) {
    header('location:index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçete #<?php echo $recete_id; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 40px; color: #333; }
        .recete { max-width: 700px; margin: 0 auto; border: 2px solid #1a73e8; border-radius: 12px; padding: 35px; }
        .header { text-align: center; border-bottom: 2px solid #1a73e8; padding-bottom: 20px; margin-bottom: 25px; }
        .header h1 { font-size: 20px; color: #1a73e8; margin-bottom: 4px; }
        .header p { color: #666; font-size: 13px; }
        .meta { margin-bottom: 25px; font-size: 13px; color: #555; }
        .meta div { margin-bottom: 4px; }
        .meta strong { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #1a73e8; color: #fff; padding: 8px 12px; font-size: 12px; text-align: left; }
        td { padding: 8px 12px; border-bottom: 1px solid #e0e0e0; font-size: 13px; }
        tr:nth-child(even) { background: #f8f9fa; }
        .footer { text-align: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 11px; color: #999; }
        .no-print { text-align: center; margin-bottom: 15px; }
        .print-btn { padding: 8px 20px; background: #1a73e8; color: #fff; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 13px; display: inline-block; }
        @media print { .no-print { display: none; } body { padding: 10px; } .recete { border: 1px solid #ccc; } }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="javascript:window.print()" class="print-btn">🖨️ Yazdır / PDF</a>
        <a href="<?php echo $rol === 'doktor' ? 'doktor_panel.php' : 'randevu.php'; ?>" class="print-btn" style="background:#666;">← Geri</a>
    </div>

    <div class="recete">
        <div class="header">
            <h1>HASTANE OTOMASYONU</h1>
            <p>REÇETE BELGESİ</p>
        </div>

        <div class="meta">
            <div><strong>Hasta:</strong> <?php echo htmlspecialchars($recete['hasta_ad']); ?> (<?php echo htmlspecialchars($recete['hasta_tc']); ?>)</div>
            <div><strong>Doktor:</strong> <?php echo htmlspecialchars($recete['doktor_ad']); ?></div>
            <div><strong>Tarih:</strong> <?php echo date('d.m.Y H:i', strtotime($recete['olusturma_tarihi'])); ?></div>
            <div><strong>Reçete No:</strong> #<?php echo $recete_id; ?></div>
        </div>

        <table>
            <thead>
                <tr><th style="width:40px;">#</th><th>İlaç</th><th>Doz</th><th>Süre</th><th>Açıklama</th></tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($ilaclar as $ilac_satir):
                    $parcalar = array_map('trim', explode('|', $ilac_satir));
                ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><strong><?php echo htmlspecialchars($parcalar[0] ?? ''); ?></strong></td>
                    <td><?php echo htmlspecialchars($parcalar[1] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($parcalar[2] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($parcalar[3] ?? '-'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="footer">
            Bu belge Hastane Otomasyonu tarafından oluşturulmuştur. &mdash; <?php echo date('Y'); ?>
        </div>
    </div>
</body>
</html>
