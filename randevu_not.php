<?php
include 'header.php';

if (!isset($_SESSION['kullanici_tc'])) {
    header('location:index.php');
    exit;
}

$rol = kullanici_rol();
$randevu_id = isset($_GET['randevu_id']) ? intval($_GET['randevu_id']) : 0;
$doktor_id = $_SESSION['doktor_id'] ?? 0;

if (!$randevu_id) {
    header('location:' . ($rol === 'doktor' ? 'doktor_panel.php' : 'randevu.php'));
    exit;
}

try {
    if ($rol === 'doktor') {
        $rs = $db->prepare("SELECT r.*, k.kullanici_adsoyad, k.kullanici_tc FROM randevu r JOIN kullanici k ON r.kullanici_id = k.kullanici_id WHERE r.randevu_id = ? AND r.doktor_id = ?");
        $rs->execute([$randevu_id, $doktor_id]);
    } else {
        $k_id = $db->prepare('SELECT kullanici_id FROM kullanici WHERE kullanici_tc = ?');
        $k_id->execute([$_SESSION['kullanici_tc']]);
        $ku = $k_id->fetch(PDO::FETCH_ASSOC);
        $rs = $db->prepare("SELECT r.*, k.kullanici_adsoyad, k.kullanici_tc FROM randevu r JOIN kullanici k ON r.kullanici_id = k.kullanici_id WHERE r.randevu_id = ? AND r.kullanici_id = ?");
        $rs->execute([$randevu_id, $ku['kullanici_id']]);
    }
    $randevu = $rs->fetch(PDO::FETCH_ASSOC);
    if (!$randevu) {
        header('location:index.php');
        exit;
    }

    $notlar = $db->prepare("SELECT rn.*, k.kullanici_adsoyad AS doktor_ad FROM randevu_not rn JOIN doktor d ON rn.doktor_id = d.doktor_id JOIN kullanici k ON d.kullanici_id = k.kullanici_id WHERE rn.randevu_id = ? ORDER BY rn.olusturma_tarihi DESC");
    $notlar->execute([$randevu_id]);
    $not_listesi = $notlar->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    header('location:' . ($rol === 'doktor' ? 'doktor_panel.php' : 'randevu.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Tıbbi Not - <?php echo htmlspecialchars($randevu['kullanici_adsoyad']); ?></title>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="page-container">
        <div class="page-header">
            <h2>📝 Tıbbi Not</h2>
            <p>Hasta: <strong><?php echo htmlspecialchars($randevu['kullanici_adsoyad']); ?></strong> (<?php echo htmlspecialchars($randevu['kullanici_tc']); ?>)</p>
        </div>

        <?php if (isset($_GET['durum'])) mesaj_goster($_GET['durum']); ?>

        <div class="dashboard-grid">
            <?php if ($rol === 'doktor'): ?>
            <div class="card">
                <h3 class="card-title"><span class="card-icon">➕</span> Not Ekle</h3>
                <form action="islem.php" method="post">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="not_randevu_id" value="<?php echo $randevu_id; ?>">
                    <div class="form-group">
                        <label for="not_metni">Not</label>
                        <textarea id="not_metni" name="not_metni" class="form-control" rows="6" placeholder="Hastanın durumu, tanı, tedavi notları..." required></textarea>
                    </div>
                    <button type="submit" name="randevu_not_kaydet" class="btn btn-primary">Notu Kaydet</button>
                </form>
            </div>
            <?php endif; ?>

            <div class="card">
                <h3 class="card-title"><span class="card-icon">📋</span> Not Geçmişi</h3>
                <?php if (empty($not_listesi)): ?>
                <p style="color:var(--text-muted);padding:20px;text-align:center;">Henüz not eklenmemiş.</p>
                <?php else: ?>
                <div style="padding:0 20px 20px;">
                    <?php foreach ($not_listesi as $n): ?>
                    <div style="background:var(--hover-bg);border-radius:8px;padding:12px 16px;margin-bottom:10px;border-left:3px solid var(--primary);">
                        <div style="font-size:11px;color:var(--text-muted);margin-bottom:6px;">
                            <?php echo date('d.m.Y H:i', strtotime($n['olusturma_tarihi'])); ?> — <strong><?php echo htmlspecialchars($n['doktor_ad']); ?></strong>
                        </div>
                        <div style="font-size:14px;line-height:1.6;white-space:pre-wrap;"><?php echo htmlspecialchars($n['not_metni']); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div style="margin-top:20px;">
            <a href="<?php echo $rol === 'doktor' ? 'doktor_panel.php' : 'randevu.php'; ?>" class="btn btn-primary">← Geri Dön</a>
        </div>
    </div>
</body>
</html>
