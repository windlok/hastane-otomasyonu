<?php 
include 'header.php';
hasta_kontrol();

// Kullanıcı bilgilerini alalım
try {
    $kullanici_sorgu = $db->prepare('SELECT kullanici_id, kullanici_tc, kullanici_adsoyad FROM kullanici WHERE kullanici_tc = ?');
    $kullanici_sorgu->execute([$_SESSION['kullanici_tc']]);
    $kullanici = $kullanici_sorgu->fetch(PDO::FETCH_ASSOC);
    
    if (!$kullanici) {
        header('location:islem.php?islem=cikis');
        exit;
    }
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hastane Otomasyonu - Randevularım</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="page-container">
        <div class="page-header">
            <h2>Randevu Bilgileri</h2>
            <p>Aldığınız randevuların listesi ve güncel randevu durumlarınız.</p>
        </div>

        <?php 
        // İstatistikleri hesaplayalım
        try {
            // Toplam randevu sayısı
            $toplam_sorgu = $db->prepare("SELECT COUNT(*) FROM randevu WHERE kullanici_id = ?");
            $toplam_sorgu->execute([$kullanici['kullanici_id']]);
            $toplam_randevu = $toplam_sorgu->fetchColumn();

            // Aktif/Gelecek randevu sayısı
            $aktif_sorgu = $db->prepare("SELECT COUNT(*) FROM randevu WHERE kullanici_id = ? AND randevu_tarih >= CURDATE()");
            $aktif_sorgu->execute([$kullanici['kullanici_id']]);
            $aktif_randevu = $aktif_sorgu->fetchColumn();

            // Geçmiş randevu sayısı
            $gecmis_randevu = $toplam_randevu - $aktif_randevu;
        } catch (PDOException $e) {
            $toplam_randevu = 0;
            $aktif_randevu = 0;
            $gecmis_randevu = 0;
        }
        ?>

        <!-- İstatistik Satırı -->
        <div class="stat-row">
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-value"><?php echo $toplam_randevu; ?></div>
                <div class="stat-label">Toplam Randevu</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🔔</div>
                <div class="stat-value" style="color: var(--accent);"><?php echo $aktif_randevu; ?></div>
                <div class="stat-label">Yaklaşan Randevu</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-value" style="color: var(--text-light);"><?php echo $gecmis_randevu; ?></div>
                <div class="stat-label">Geçmiş Randevu</div>
            </div>
        </div>

        <?php 
        if (isset($_GET['durum'])) {
            mesaj_goster($_GET['durum']);
        }
        ?>

        <!-- Randevu Listesi Kartı -->
        <div class="card" style="padding: 20px 0;">
            <div style="padding: 0 30px 15px 30px;">
                <h3 class="card-title" style="margin-bottom: 0; border: none; padding-bottom: 0;">
                    <span class="card-icon">📋</span> Randevu Geçmişi
                </h3>
            </div>
            
            <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Hastane</th>
                                <th>Klinik</th>
                                <th>Doktor</th>
                                <th>İl</th>
                                <th>Tarih</th>
                                <th>Saat</th>
                                <th>Durum</th>
                                <th>Not</th>
                                <th>Reçete</th>
                                <th style="width: 140px; text-align: center;">İşlem</th>
                            </tr>
                        </thead>
                    <tbody>
                        <?php
                        // Randevuları listele
                        try {
                            $sorgu = $db->prepare("SELECT * FROM randevu WHERE kullanici_id = ? ORDER BY randevu_tarih DESC");
                            $sorgu->execute([$kullanici['kullanici_id']]);
                            
                            if ($sorgu->rowCount() == 0) {
                                echo "<tr><td colspan='9' class='empty-state'>
                                    <span class='empty-icon'>📅</span>
                                    <p>Henüz randevunuz bulunmamaktadır.</p>
                                </td></tr>";
                            }

                            while ($randevu = $sorgu->fetch(PDO::FETCH_ASSOC)) {
                                $tarih_format = date('d.m.Y', strtotime($randevu['randevu_tarih']));
                                $saat_format = $randevu['randevu_saat'] ? date('H:i', strtotime($randevu['randevu_saat'])) : '-';
                                $randevu_zamani = strtotime($randevu['randevu_tarih'] . ' ' . ($randevu['randevu_saat'] ?? '23:59:59'));
                                $is_future = $randevu_zamani >= time();

                                // Not var mı?
                                $not_say = $db->prepare("SELECT COUNT(*) FROM randevu_not WHERE randevu_id = ?");
                                $not_say->execute([$randevu['randevu_id']]);
                                $not_var = $not_say->fetchColumn() > 0;

                                // Reçete var mı?
                                $recete_say = $db->prepare("SELECT recete_id FROM recete WHERE randevu_id = ? ORDER BY olusturma_tarihi DESC LIMIT 1");
                                $recete_say->execute([$randevu['randevu_id']]);
                                $recete_row = $recete_say->fetch(PDO::FETCH_ASSOC);
                                $recete_var = $recete_row !== false;

                                $durum = $randevu['durum'] ?? 'aktif';
                                if ($durum === 'iptal') {
                                    $durum_renk = '#e74c3c';
                                    $durum_text = 'İptal Edildi';
                                } elseif (!$is_future) {
                                    $durum_renk = 'var(--text-muted)';
                                    $durum_text = 'Gerçekleşti';
                                } else {
                                    $durum_renk = 'var(--success)';
                                    $durum_text = 'Aktif';
                                }

                                echo "<tr>";
                                echo "<td><strong>" . htmlspecialchars($randevu['randevu_hastane']) . "</strong></td>";
                                echo "<td>" . htmlspecialchars($randevu['randevu_klinik']) . "</td>";
                                echo "<td>" . htmlspecialchars($randevu['randevu_doktoru']) . "</td>";
                                echo "<td>" . htmlspecialchars($randevu['randevu_sehir']) . "</td>";
                                echo "<td>" . $tarih_format . "</td>";
                                echo "<td><span class='slot-badge active'>" . $saat_format . "</span></td>";
                                echo "<td style='text-align: center;'><span style='color: " . $durum_renk . "; font-weight: 600; font-size: 13px;'>" . $durum_text . "</span></td>";
                                echo "<td style='text-align: center;'>" . ($not_var ? "<a href='randevu_not.php?randevu_id=" . $randevu['randevu_id'] . "' style='color:var(--primary);font-size:12px;'>📋 Gör</a>" : "<span style='color:var(--text-muted);font-size:11px;'>-</span>") . "</td>";
                                echo "<td style='text-align: center;'>" . ($recete_var ? "<a href='recete_goruntule.php?recete_id=" . $recete_row['recete_id'] . "' style='color:var(--accent);font-size:12px;'>💊 Gör</a>" : "<span style='color:var(--text-muted);font-size:11px;'>-</span>") . "</td>";
                                echo "<td style='text-align: center;'>";
                                if ($is_future && $durum === 'aktif') {
                                    echo "<div style='display: flex; gap: 6px; justify-content: center; flex-wrap: wrap;'>";
                                    echo "<a href='anasayfa.php?duzenle=" . $randevu['randevu_id'] . "' class='btn btn-primary' style='padding: 6px 14px; font-size: 12px;'>Düzenle</a>";
                                    echo "<form action='islem.php' method='post' style='display:inline;' onsubmit='return confirm(\"Randevunuzu iptal etmek istediğinize emin misiniz?\")'>";
                                    echo "<input type='hidden' name='csrf_token' value='" . $_SESSION['csrf_token'] . "'>";
                                    echo "<input type='hidden' name='randevu_sil_id' value='" . $randevu['randevu_id'] . "'>";
                                    echo "<button type='submit' name='randevu_sil' class='btn btn-danger' style='padding: 6px 14px; font-size: 12px;'>İptal</button>";
                                    echo "</form>";
                                    echo "</div>";
                                } else {
                                    echo "<span style='color: var(--text-muted); font-size: 13px; font-weight: 500;'>-</span>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='7' class='empty-state'>Veriler yüklenirken bir sorun oluştu.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>