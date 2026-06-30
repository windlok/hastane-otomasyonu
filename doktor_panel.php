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
            <a href="?filtre=saatler" class="filter-btn <?php echo $filtre === 'saatler' ? 'active' : ''; ?>">🕐 Çalışma Saatlerim</a>
        </div>

        <?php if ($filtre === 'saatler'): 
            $gunler = ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'];
            $calisma_saat = $db->prepare('SELECT gun, baslangic, bitis FROM doktor_calisma_saat WHERE doktor_id = ? ORDER BY gun');
            $calisma_saat->execute([$doktor_id]);
            $mevcut_saatler = [];
            foreach ($calisma_saat->fetchAll(PDO::FETCH_ASSOC) as $cs) {
                $mevcut_saatler[$cs['gun']] = $cs;
            }
        ?>
        <div class="card" style="margin-top: 20px;">
            <h3 class="card-title"><span class="card-icon">🕐</span> Çalışma Saatlerim</h3>
            <p style="padding: 0 30px; color: var(--text-muted); font-size: 14px;">Haftanın her günü için çalışma saatlerinizi belirleyin. Randevu alırken sadece bu saat aralığındaki slotlar gösterilir.</p>
            <div style="padding: 0 30px 20px;">
                <form action="islem.php" method="post">
                    <?php echo csrf_input(); ?>
                    <table style="width:100%;">
                        <thead>
                            <tr>
                                <th style="text-align:left; padding:8px;">Gün</th>
                                <th style="text-align:left; padding:8px;">Çalışıyor</th>
                                <th style="text-align:left; padding:8px;">Başlangıç</th>
                                <th style="text-align:left; padding:8px;">Bitiş</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 1; $i <= 5; $i++): 
                                $ms = $mevcut_saatler[$i] ?? null;
                                $baslangic = $ms ? substr($ms['baslangic'], 0, 5) : '09:00';
                                $bitis = $ms ? substr($ms['bitis'], 0, 5) : '17:00';
                            ?>
                            <tr>
                                <td style="padding:8px;"><strong><?php echo $gunler[$i-1]; ?></strong></td>
                                <td style="padding:8px;">
                                    <select name="calisma_gun_<?php echo $i; ?>" class="select-control" style="width:auto; min-width:80px;">
                                        <option value="1" <?php echo $ms ? 'selected' : ''; ?>>Evet</option>
                                        <option value="0" <?php echo !$ms ? 'selected' : ''; ?>>Hayır</option>
                                    </select>
                                </td>
                                <td style="padding:8px;">
                                    <input type="time" name="baslangic_<?php echo $i; ?>" class="form-control" style="width:120px;" value="<?php echo $baslangic; ?>">
                                </td>
                                <td style="padding:8px;">
                                    <input type="time" name="bitis_<?php echo $i; ?>" class="form-control" style="width:120px;" value="<?php echo $bitis; ?>">
                                </td>
                            </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                    <button type="submit" name="doktor_saat_kaydet" class="btn btn-primary" style="margin-top:15px;">Saatleri Kaydet</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

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
                            <th style="width:100px;">Not</th>
                            <th style="width:100px;">Reçete</th>
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

                            // Not kontrolü
                            $not_varmi = $db->prepare("SELECT not_id, LEFT(not_metni, 50) AS ozet, olusturma_tarihi FROM randevu_not WHERE randevu_id = ? AND doktor_id = ? ORDER BY olusturma_tarihi DESC LIMIT 1");
                            $not_varmi->execute([$r['randevu_id'], $doktor_id]);
                            $not_row = $not_varmi->fetch(PDO::FETCH_ASSOC);

                            // Reçete kontrolü
                            $recete_varmi = $db->prepare("SELECT recete_id FROM recete WHERE randevu_id = ? AND doktor_id = ? ORDER BY olusturma_tarihi DESC LIMIT 1");
                            $recete_varmi->execute([$r['randevu_id'], $doktor_id]);
                            $recete_row = $recete_varmi->fetch(PDO::FETCH_ASSOC);
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
                            <td>
                                <a href="randevu_not.php?randevu_id=<?php echo $r['randevu_id']; ?>" class="btn btn-sm" style="background:#e3f2fd;color:#1565c0;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:11px;font-weight:600;text-decoration:none;display:inline-block;">
                                    <?php echo $not_row ? '📝 Notu Gör' : '➕ Not Ekle'; ?>
                                </a>
                                <?php if ($not_row): ?>
                                <div style="font-size:10px;color:var(--text-muted);margin-top:3px;"><?php echo htmlspecialchars($not_row['ozet']); ?>...</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($recete_row): ?>
                                <a href="recete_goruntule.php?recete_id=<?php echo $recete_row['recete_id']; ?>" class="btn btn-sm" style="background:#fff3e0;color:#e65100;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:11px;font-weight:600;text-decoration:none;display:inline-block;">📋 Gör</a>
                                <?php else: ?>
                                <a href="recete.php?randevu_id=<?php echo $r['randevu_id']; ?>" class="btn btn-sm" style="background:#e3f2fd;color:#1565c0;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:11px;font-weight:600;text-decoration:none;display:inline-block;">➕ Reçete Yaz</a>
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
