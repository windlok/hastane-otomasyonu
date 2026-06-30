<?php
include 'header.php';
doktor_kontrol();

$doktor_id = $_SESSION['doktor_id'] ?? null;
$filtre = isset($_GET['filtre']) ? $_GET['filtre'] : 'yaklasan';

if (!$doktor_id) {
    header('location:islem.php?islem=cikis');
    exit;
}

try {
    $doktor_sorgu = $db->prepare('SELECT d.*, k.kullanici_adsoyad FROM doktor d JOIN kullanici k ON d.kullanici_id = k.kullanici_id WHERE d.doktor_id = ?');
    $doktor_sorgu->execute([$doktor_id]);
    $doktor = $doktor_sorgu->fetch(PDO::FETCH_ASSOC);

    $bugun = date('Y-m-d');

    $toplam = $db->prepare("SELECT COUNT(*) FROM randevu WHERE doktor_id = ? AND durum = 'aktif'");
    $toplam->execute([$doktor_id]);
    $toplam_randevu = $toplam->fetchColumn();

    $bugun_sorgu = $db->prepare("SELECT COUNT(*) FROM randevu WHERE doktor_id = ? AND randevu_tarih = ? AND durum = 'aktif'");
    $bugun_sorgu->execute([$doktor_id, $bugun]);
    $bugun_randevu = $bugun_sorgu->fetchColumn();

    $yaklasan_sorgu = $db->prepare("SELECT COUNT(*) FROM randevu WHERE doktor_id = ? AND randevu_tarih >= ? AND durum = 'aktif'");
    $yaklasan_sorgu->execute([$doktor_id, $bugun]);
    $yaklasan_randevu = $yaklasan_sorgu->fetchColumn();

    $sql = "SELECT r.*, k.kullanici_adsoyad, k.kullanici_tc, k.kullanici_telefon, k.kullanici_email
            FROM randevu r
            JOIN kullanici k ON r.kullanici_id = k.kullanici_id
            WHERE r.doktor_id = ? AND r.durum = 'aktif'";

    if ($filtre === 'bugun') {
        $sql .= " AND r.randevu_tarih = ?";
        $params = [$doktor_id, $bugun];
    } elseif ($filtre === 'yaklasan') {
        $sql .= " AND r.randevu_tarih >= ?";
        $params = [$doktor_id, $bugun];
    } else {
        $params = [$doktor_id];
    }

    $sql .= " ORDER BY r.randevu_tarih ASC, r.randevu_saat ASC";
    $randevu_sorgu = $db->prepare($sql);
    $randevu_sorgu->execute($params);
    $randevular = $randevu_sorgu->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Veritabanı hatası');
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Hastane Otomasyonu - Doktor Paneli</title>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="page-container">
        <div class="page-header">
            <h2>Doktor Paneli</h2>
            <p><?php echo htmlspecialchars($doktor['kullanici_adsoyad']); ?> — <?php echo htmlspecialchars($doktor['klinik']); ?>, <?php echo htmlspecialchars($doktor['hastane']); ?></p>
        </div>

        <?php if (isset($_GET['durum'])) mesaj_goster($_GET['durum']); ?>

        <div class="stat-row">
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-value"><?php echo $toplam_randevu; ?></div>
                <div class="stat-label">Toplam Aktif Randevu</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🩺</div>
                <div class="stat-value" style="color: var(--accent);"><?php echo $bugun_randevu; ?></div>
                <div class="stat-label">Bugünkü Hasta</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🔔</div>
                <div class="stat-value"><?php echo $yaklasan_randevu; ?></div>
                <div class="stat-label">Yaklaşan Randevu</div>
            </div>
        </div>

        <div class="filter-bar">
            <a href="?filtre=bugun" class="filter-btn <?php echo $filtre === 'bugun' ? 'active' : ''; ?>">Bugün</a>
            <a href="?filtre=yaklasan" class="filter-btn <?php echo $filtre === 'yaklasan' ? 'active' : ''; ?>">Yaklaşan</a>
            <a href="?filtre=tumu" class="filter-btn <?php echo $filtre === 'tumu' ? 'active' : ''; ?>">Tümü</a>
        </div>

        <div class="card" style="padding: 20px 0;">
            <div style="padding: 0 30px 15px 30px;">
                <h3 class="card-title" style="margin-bottom: 0; border: none; padding-bottom: 0;">
                    <span class="card-icon">👥</span> Hasta Randevu Listesi
                </h3>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Tarih</th>
                            <th>Saat</th>
                            <th>Hasta Ad Soyad</th>
                            <th>TC Kimlik</th>
                            <th>Telefon</th>
                            <th>E-posta</th>
                            <th>Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($randevular)): ?>
                        <tr>
                            <td colspan="7" class="empty-state">
                                <span class="empty-icon">📋</span>
                                <p>Bu filtreye uygun randevu bulunmuyor.</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($randevular as $r):
                            $tarih_fmt = date('d.m.Y', strtotime($r['randevu_tarih']));
                            $saat_fmt = $r['randevu_saat'] ? date('H:i', strtotime($r['randevu_saat'])) : '-';
                            $gecmis = strtotime($r['randevu_tarih'] . ' ' . ($r['randevu_saat'] ?? '00:00:00')) < time();
                        ?>
                        <tr>
                            <td><strong><?php echo $tarih_fmt; ?></strong></td>
                            <td><span class="slot-badge active"><?php echo $saat_fmt; ?></span></td>
                            <td><?php echo htmlspecialchars($r['kullanici_adsoyad']); ?></td>
                            <td><?php echo htmlspecialchars($r['kullanici_tc']); ?></td>
                            <td><?php echo htmlspecialchars($r['kullanici_telefon'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($r['kullanici_email'] ?: '-'); ?></td>
                            <td>
                                <?php if ($gecmis): ?>
                                <span class="badge badge-muted">Geçmiş</span>
                                <?php elseif ($r['randevu_tarih'] === $bugun): ?>
                                <span class="badge badge-warning">Bugün</span>
                                <?php else: ?>
                                <span class="badge badge-success">Bekliyor</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
