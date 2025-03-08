<?php 
include 'header.php';

// Oturum kontrolü
if(!isset($_SESSION['kullanici_tc'])) {
    header('location:index.php');
    exit;
}

// Kullanıcı bilgilerini alalım
$kullanici_sorgu = $db->prepare('SELECT kullanici_id, kullanici_tc, kullanici_adsoyad FROM kullanici WHERE kullanici_tc = ?');
$kullanici_sorgu->execute([$_SESSION['kullanici_tc']]);
$kullanici = $kullanici_sorgu->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hastane Otomasyonu</title>
    <style>
        .hesap-bilgileri {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .hesap-bilgileri h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .hesap-bilgileri p {
            margin: 5px 0;
            color: #666;
        }
        .hesap-bilgileri strong {
            color: #333;
        }
        .sil-btn {
            background-color: #ff4444;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
        }
        .sil-btn:hover {
            background-color: #cc0000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .mesaj {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
        }
        .basarili {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .hata {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    

    <?php 
    if(isset($_GET['durum'])) {
        $mesaj = '';
        $mesaj_sinifi = '';
        
        switch($_GET['durum']) {
            case 'basarili':
                $mesaj = 'Randevu başarıyla kaydedildi!';
                $mesaj_sinifi = 'basarili';
                break;
            case 'silindi':
                $mesaj = 'Randevu başarıyla silindi!';
                $mesaj_sinifi = 'basarili';
                break;
            case 'hata':
                $mesaj = 'Bir hata oluştu!';
                $mesaj_sinifi = 'hata';
                break;
            case 'bos_alan':
                $mesaj = 'Lütfen tüm alanları doldurun!';
                $mesaj_sinifi = 'hata';
                break;
        }
        
        if($mesaj) {
            echo '<div class="mesaj ' . $mesaj_sinifi . '">' . $mesaj . '</div>';
        }
    }
    ?>
    <table>
        <tr>
            <th>Hastane</th>
            <th>Klinik</th>
            <th>Doktor</th>
            <th>İl</th>
            <th>Tarih</th>
            <th>İşlem</th>
        </tr>
        <?php
        // Randevuları listele
        $sorgu = $db->prepare("SELECT * FROM randevu WHERE kullanici_id = ? ORDER BY randevu_tarih DESC");
        $sorgu->execute([$kullanici['kullanici_id']]);
        
        if($sorgu->rowCount() == 0) {
            echo "<tr><td colspan='6'>Henüz randevunuz bulunmamaktadır.</td></tr>";
        }
        
        while($randevu = $sorgu->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($randevu['randevu_hastane']) . "</td>";
            echo "<td>" . htmlspecialchars($randevu['randevu_klinik']) . "</td>";
            echo "<td>" . htmlspecialchars($randevu['randevu_doktoru']) . "</td>";
            echo "<td>" . htmlspecialchars($randevu['randevu_sehir']) . "</td>";
            echo "<td>" . htmlspecialchars($randevu['randevu_tarih']) . "</td>";
            echo "<td><a href='islem.php?islem=randevu_sil&id=" . $randevu['randevu_id'] . "' onclick='return confirm(\"Randevuyu silmek istediğinize emin misiniz?\")' class='sil-btn'>Sil</a></td>";
            echo "</tr>";
        }
        ?>
    </table>
</body>
</html>