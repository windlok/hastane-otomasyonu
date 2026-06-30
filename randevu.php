<?php 
include 'header.php';

// Oturum kontrolü
if(!isset($_SESSION['kullanici_tc'])) {
    header('location:index.php');
    exit;
}

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
                            <th style="width: 100px; text-align: center;">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Randevuları listele
                        try {
                            $sorgu = $db->prepare("SELECT * FROM randevu WHERE kullanici_id = ? ORDER BY randevu_tarih DESC");
                            $sorgu->execute([$kullanici['kullanici_id']]);
                            
                            if ($sorgu->rowCount() == 0) {
                                echo "<tr><td colspan='6' class='empty-state'>
                                    <span class='empty-icon'>📅</span>
                                    <p>Henüz randevunuz bulunmamaktadır.</p>
                                </td></tr>";
                            }
                            
                            while ($randevu = $sorgu->fetch(PDO::FETCH_ASSOC)) {
                                $tarih_format = date('d.m.Y', strtotime($randevu['randevu_tarih']));
                                $is_future = strtotime($randevu['randevu_tarih']) >= strtotime(date('Y-m-d'));
                                
                                echo "<tr>";
                                echo "<td><strong>" . htmlspecialchars($randevu['randevu_hastane']) . "</strong></td>";
                                echo "<td>" . htmlspecialchars($randevu['randevu_klinik']) . "</td>";
                                echo "<td>" . htmlspecialchars($randevu['randevu_doktoru']) . "</td>";
                                echo "<td>" . htmlspecialchars($randevu['randevu_sehir']) . "</td>";
                                echo "<td>" . $tarih_format . "</td>";
                                echo "<td style='text-align: center;'>";
                                if ($is_future) {
                                    echo "<a href='islem.php?islem=randevu_sil&id=" . $randevu['randevu_id'] . "' onclick='return confirm(\"Randevunuzu iptal etmek istediğinize emin misiniz?\")' class='btn btn-danger'>İptal Et</a>";
                                } else {
                                    echo "<span style='color: var(--text-muted); font-size: 13px; font-weight: 500;'>Gerçekleşti</span>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='6' class='empty-state'>Veriler yüklenirken bir sorun oluştu.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>