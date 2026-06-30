<?php
include 'header.php';
doktor_kontrol();

$randevu_id = isset($_GET['randevu_id']) ? intval($_GET['randevu_id']) : 0;
$doktor_id = $_SESSION['doktor_id'] ?? 0;

if (!$randevu_id || !$doktor_id) {
    header('location:doktor_panel.php');
    exit;
}

try {
    $rs = $db->prepare("SELECT r.*, k.kullanici_adsoyad, k.kullanici_tc FROM randevu r JOIN kullanici k ON r.kullanici_id = k.kullanici_id WHERE r.randevu_id = ? AND r.doktor_id = ?");
    $rs->execute([$randevu_id, $doktor_id]);
    $randevu = $rs->fetch(PDO::FETCH_ASSOC);
    if (!$randevu) {
        header('location:doktor_panel.php');
        exit;
    }
} catch (PDOException $e) {
    header('location:doktor_panel.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Reçete - <?php echo htmlspecialchars($randevu['kullanici_adsoyad']); ?></title>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="page-container">
        <div class="page-header">
            <h2>📋 Reçete Yaz</h2>
            <p>Hasta: <strong><?php echo htmlspecialchars($randevu['kullanici_adsoyad']); ?></strong> (<?php echo htmlspecialchars($randevu['kullanici_tc']); ?>)</p>
        </div>

        <?php if (isset($_GET['durum'])) mesaj_goster($_GET['durum']); ?>

        <div class="card">
            <h3 class="card-title"><span class="card-icon">💊</span> İlaç Listesi</h3>
            <form action="islem.php" method="post">
                <?php echo csrf_input(); ?>
                <input type="hidden" name="recete_randevu_id" value="<?php echo $randevu_id; ?>">
                <div class="form-group">
                    <label for="ilaclar">İlaçlar (Her satıra bir ilaç: İlaç Adı | Doz | Süre | Açıklama)</label>
                    <textarea id="ilaclar" name="ilaclar" class="form-control" rows="8" placeholder="Örnek:
Amoksisilin | 500mg | 7 gün | Günde 2 kez
Parol | 500mg | 5 gün | Günde 3 kez yemeklerden sonra
İbuprofen | 200mg | 3 gün | Günde 2 kez" required></textarea>
                </div>
                <button type="submit" name="recete_kaydet" class="btn btn-primary">Reçeteyi Kaydet</button>
                <a href="doktor_panel.php" class="btn btn-secondary" style="margin-left:8px;">İptal</a>
            </form>
        </div>
    </div>
</body>
</html>
