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
    die(__('hata_sistem') . ": " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('site_title'); ?> - <?php echo __('randevularim'); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="page-container">
        <div class="page-header">
            <h2><?php echo __('randevu_bilgileri'); ?></h2>
            <p><?php echo __('randevu_liste_aciklama'); ?></p>
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
                <div class="stat-label"><?php echo __('toplam_randevu'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🔔</div>
                <div class="stat-value" style="color: var(--accent);"><?php echo $aktif_randevu; ?></div>
                <div class="stat-label"><?php echo __('aktif_randevu'); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-value" style="color: var(--text-light);"><?php echo $gecmis_randevu; ?></div>
                <div class="stat-label"><?php echo __('gecmis_randevu'); ?></div>
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
                    <span class="card-icon">📋</span> <?php echo __('randevu_gecmisi'); ?>
                </h3>
            </div>
            
            <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo __('hastane'); ?></th>
                                <th><?php echo __('klinik'); ?></th>
                                <th><?php echo __('doktor'); ?></th>
                                <th><?php echo __('sehir'); ?></th>
                                <th><?php echo __('tarih'); ?></th>
                                <th><?php echo __('saat'); ?></th>
                                <th><?php echo __('durum'); ?></th>
                                <th><?php echo __('notlar'); ?></th>
                                <th><?php echo __('recete'); ?></th>
                                <th style="width: 140px; text-align: center;"><?php echo __('islem'); ?></th>
                            </tr>
                        </thead>
                    <tbody>
                        <?php
                        // Randevuları listele
                        try {
                            $sorgu = $db->prepare("SELECT * FROM randevu WHERE kullanici_id = ? ORDER BY randevu_tarih DESC");
                            $sorgu->execute([$kullanici['kullanici_id']]);
                            
                            if ($sorgu->rowCount() == 0) {
                                echo "<tr><td colspan='10' class='empty-state'>
                                    <span class='empty-icon'>📅</span>
                                    <p><?php echo __('henuz_randevu_yok'); ?></p>
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
                                    $durum_text = __('iptal');
                                } elseif (!$is_future) {
                                    $durum_renk = 'var(--text-muted)';
                                    $durum_text = __('gerceklesti');
                                } else {
                                    $durum_renk = 'var(--success)';
                                    $durum_text = __('aktif');
                                }

                                echo "<tr>";
                                echo "<td><strong>" . htmlspecialchars($randevu['randevu_hastane']) . "</strong></td>";
                                echo "<td>" . htmlspecialchars($randevu['randevu_klinik']) . "</td>";
                                echo "<td>" . htmlspecialchars($randevu['randevu_doktoru']) . "</td>";
                                echo "<td>" . htmlspecialchars($randevu['randevu_sehir']) . "</td>";
                                echo "<td>" . $tarih_format . "</td>";
                                echo "<td><span class='slot-badge active'>" . $saat_format . "</span></td>";
                                echo "<td style='text-align: center;'><span style='color: " . $durum_renk . "; font-weight: 600; font-size: 13px;'>" . $durum_text . "</span></td>";
                                echo "<td style='text-align: center;'>" . ($not_var ? "<a href='randevu_not.php?randevu_id=" . $randevu['randevu_id'] . "' style='color:var(--primary);font-size:12px;'>📋 " . __('notu_gor') . "</a>" : "<span style='color:var(--text-muted);font-size:11px;'>-</span>") . "</td>";
                                echo "<td style='text-align: center;'>" . ($recete_var ? "<a href='recete_goruntule.php?recete_id=" . $recete_row['recete_id'] . "' style='color:var(--accent);font-size:12px;'>💊 " . __('recete_gor') . "</a>" : "<span style='color:var(--text-muted);font-size:11px;'>-</span>") . "</td>";
                                echo "<td style='text-align: center;'>";
                                if ($is_future && $durum === 'aktif') {
                                    echo "<div style='display: flex; gap: 6px; justify-content: center; flex-wrap: wrap;'>";
                                    echo "<a href='anasayfa.php?duzenle=" . $randevu['randevu_id'] . "' class='btn btn-primary' style='padding: 6px 14px; font-size: 12px;'>" . __('duzenle') . "</a>";
                                    echo "<form action='islem.php' method='post' style='display:inline;' onsubmit='return confirm(\"" . __('iptal_onay') . "\")'>";
                                    echo "<input type='hidden' name='csrf_token' value='" . $_SESSION['csrf_token'] . "'>";
                                    echo "<input type='hidden' name='randevu_sil_id' value='" . $randevu['randevu_id'] . "'>";
                                    echo "<button type='submit' name='randevu_sil' class='btn btn-danger' style='padding: 6px 14px; font-size: 12px;'>" . __('iptal_et') . "</button>";
                                    echo "</form>";
                                    echo "</div>";
                                } else {
                                    echo "<span style='color: var(--text-muted); font-size: 13px; font-weight: 500;'>-</span>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='10' class='empty-state'>" . __('veri_yukleme_hatasi') . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>