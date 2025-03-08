<?php 
include 'header.php';

// Oturum kontrolü
if(!isset($_SESSION['kullanici_tc'])) {
    header('location:index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Hastane Otomasyonu</title>
</head>
<body>
    <div class="adsoyad">
        <h4>Sn. <?php echo $_SESSION['kullanici_adsoyad']; ?></h4>
    </div>
    <div class="orta_div" id="randevu_div">
    
    <form action="islem.php" method="post">
    
    <input type="date" name="tarih" required>
        <select id="sehirler" name="sehirler" required>
        <option value="Adana">Adana</option>
            <option value="Adıyaman">Adıyaman</option>
            <option value="Afyonkarahisar">Afyonkarahisar</option>
            <option value="Ağrı">Ağrı</option>
            <option value="Aksaray">Aksaray</option>
            <option value="Amasya">Amasya</option>
            <option value="Ankara">Ankara</option>
            <option value="Antalya">Antalya</option>
            <option value="Ardahan">Ardahan</option>
            <option value="Artvin">Artvin</option>
            <option value="Aydın">Aydın</option>
            <option value="Balıkesir">Balıkesir</option>
            <option value="Bartın">Bartın</option>
            <option value="Batman">Batman</option>
            <option value="Bayburt">Bayburt</option>
            <option value="Bilecik">Bilecik</option>
            <option value="Bingöl">Bingöl</option>
            <option value="Bitlis">Bitlis</option>
            <option value="Bolu">Bolu</option>
            <option value="Burdur">Burdur</option>
            <option value="Bursa">Bursa</option>
            <option value="Çanakkale">Çanakkale</option>
            <option value="Çankırı">Çankırı</option>
            <option value="Çorum">Çorum</option>
            <option value="Denizli">Denizli</option>
            <option value="Diyarbakır">Diyarbakır</option>
            <option value="Düzce">Düzce</option>
            <option value="Edirne">Edirne</option>
            <option value="Elazığ">Elazığ</option>
            <option value="Erzincan">Erzincan</option>
            <option value="Erzurum">Erzurum</option>
            <option value="Eskişehir">Eskişehir</option>
            <option value="Gaziantep">Gaziantep</option>
            <option value="Giresun">Giresun</option>
            <option value="Gümüşhane">Gümüşhane</option>
            <option value="Hakkari">Hakkari</option>
            <option value="Hatay">Hatay</option>
            <option value="Iğdır">Iğdır</option>
            <option value="Isparta">Isparta</option>
            <option value="İstanbul">İstanbul</option>
            <option value="İzmir">İzmir</option>
            <option value="Kahramanmaraş">Kahramanmaraş</option>
            <option value="Karabük">Karabük</option>
            <option value="Karaman">Karaman</option>
            <option value="Kars">Kars</option>
            <option value="Kastamonu">Kastamonu</option>
            <option value="Kayseri">Kayseri</option>
            <option value="Kırıkkale">Kırıkkale</option>
            <option value="Kırklareli">Kırklareli</option>
            <option value="Kırşehir">Kırşehir</option>
            <option value="Kilis">Kilis</option>
            <option value="Kocaeli">Kocaeli</option>
            <option value="Konya">Konya</option>
            <option value="Kütahya">Kütahya</option>
            <option value="Malatya">Malatya</option>
            <option value="Manisa">Manisa</option>
            <option value="Mardin">Mardin</option>
            <option value="Mersin">Mersin</option>
            <option value="Muğla">Muğla</option>
            <option value="Muş">Muş</option>
            <option value="Nevşehir">Nevşehir</option>
            <option value="Niğde">Niğde</option>
            <option value="Ordu">Ordu</option>
            <option value="Osmaniye">Osmaniye</option>
            <option value="Rize">Rize</option>
            <option value="Sakarya">Sakarya</option>
            <option value="Samsun">Samsun</option>
            <option value="Siirt">Siirt</option>
            <option value="Sinop">Sinop</option>
            <option value="Sivas">Sivas</option>
            <option value="Şanlıurfa">Şanlıurfa</option>
            <option value="Şırnak">Şırnak</option>
            <option value="Tekirdağ">Tekirdağ</option>
            <option value="Tokat">Tokat</option>
            <option value="Trabzon">Trabzon</option>
            <option value="Tunceli">Tunceli</option>
            <option value="Uşak">Uşak</option>
            <option value="Van">Van</option>
            <option value="Yalova">Yalova</option>
            <option value="Yozgat">Yozgat</option>
            <option value="Zonguldak">Zonguldak</option>
        </select>

        <select name="hastane" class="hastane" required>
        <option value="Adana Sehir Hastanesi">Adana Şehir Hastanesi</option>
<option value="Adiyaman Sehir Hastanesi">Adıyaman Şehir Hastanesi</option>
<option value="Afyonkarahisar Sehir Hastanesi">Afyonkarahisar Şehir Hastanesi</option>
<option value="Agri Sehir Hastanesi">Ağrı Şehir Hastanesi</option>
<option value="Aksaray Sehir Hastanesi">Aksaray Şehir Hastanesi</option>
<option value="Amasya Sehir Hastanesi">Amasya Şehir Hastanesi</option>
<option value="Ankara Sehir Hastanesi">Ankara Şehir Hastanesi</option>
<option value="Antalya Sehir Hastanesi">Antalya Şehir Hastanesi</option>
<option value="Ardahan Sehir Hastanesi">Ardahan Şehir Hastanesi</option>
<option value="Artvin Sehir Hastanesi">Artvin Şehir Hastanesi</option>
<option value="Aydin Sehir Hastanesi">Aydın Şehir Hastanesi</option>
<option value="Balikesir Sehir Hastanesi">Balıkesir Şehir Hastanesi</option>
<option value="Bartin Sehir Hastanesi">Bartın Şehir Hastanesi</option>
<option value="Batman Sehir Hastanesi">Batman Şehir Hastanesi</option>
<option value="Bayburt Sehir Hastanesi">Bayburt Şehir Hastanesi</option>
<option value="Bilecik Sehir Hastanesi">Bilecik Şehir Hastanesi</option>
<option value="Bingol Sehir Hastanesi">Bingöl Şehir Hastanesi</option>
<option value="Bitlis Sehir Hastanesi">Bitlis Şehir Hastanesi</option>
<option value="Bolu Sehir Hastanesi">Bolu Şehir Hastanesi</option>
<option value="Burdur Sehir Hastanesi">Burdur Şehir Hastanesi</option>
<option value="Bursa Sehir Hastanesi">Bursa Şehir Hastanesi</option>
<option value="Canakkale Sehir Hastanesi">Çanakkale Şehir Hastanesi</option>
<option value="Cankiri Sehir Hastanesi">Çankırı Şehir Hastanesi</option>
<option value="Corum Sehir Hastanesi">Çorum Şehir Hastanesi</option>
<option value="Denizli Sehir Hastanesi">Denizli Şehir Hastanesi</option>
<option value="Diyarbakir Sehir Hastanesi">Diyarbakır Şehir Hastanesi</option>
<option value="Duzce Sehir Hastanesi">Düzce Şehir Hastanesi</option>
<option value="Edirne Sehir Hastanesi">Edirne Şehir Hastanesi</option>
<option value="Elazig Sehir Hastanesi">Elazığ Şehir Hastanesi</option>
<option value="Erzincan Sehir Hastanesi">Erzincan Şehir Hastanesi</option>
<option value="Erzurum Sehir Hastanesi">Erzurum Şehir Hastanesi</option>
<option value="Eskisehir Sehir Hastanesi">Eskişehir Şehir Hastanesi</option>
<option value="Gaziantep Sehir Hastanesi">Gaziantep Şehir Hastanesi</option>
<option value="Giresun Sehir Hastanesi">Giresun Şehir Hastanesi</option>
<option value="Gumushane Sehir Hastanesi">Gümüşhane Şehir Hastanesi</option>
<option value="Hakkari Sehir Hastanesi">Hakkari Şehir Hastanesi</option>
<option value="Hatay Sehir Hastanesi">Hatay Şehir Hastanesi</option>
<option value="Igdir Sehir Hastanesi">Iğdır Şehir Hastanesi</option>
<option value="Isparta Sehir Hastanesi">Isparta Şehir Hastanesi</option>
<option value="Istanbul Sehir Hastanesi">İstanbul Şehir Hastanesi</option>
<option value="Izmir Sehir Hastanesi">İzmir Şehir Hastanesi</option>
<option value="Kahramanmaras Sehir Hastanesi">Kahramanmaraş Şehir Hastanesi</option>
<option value="Karabuk Sehir Hastanesi">Karabük Şehir Hastanesi</option>
<option value="Karaman Sehir Hastanesi">Karaman Şehir Hastanesi</option>
<option value="Kars Sehir Hastanesi">Kars Şehir Hastanesi</option>
<option value="Kastamonu Sehir Hastanesi">Kastamonu Şehir Hastanesi</option>
<option value="Kayseri Sehir Hastanesi">Kayseri Şehir Hastanesi</option>
<option value="Kirikkale Sehir Hastanesi">Kırıkkale Şehir Hastanesi</option>
<option value="Kirklareli Sehir Hastanesi">Kırklareli Şehir Hastanesi</option>
<option value="Kirsehir Sehir Hastanesi">Kırşehir Şehir Hastanesi</option>
<option value="Kocaeli Sehir Hastanesi">Kocaeli Şehir Hastanesi</option>
<option value="Konya Sehir Hastanesi">Konya Şehir Hastanesi</option>
<option value="Kutahya Sehir Hastanesi">Kütahya Şehir Hastanesi</option>
<option value="Malatya Sehir Hastanesi">Malatya Şehir Hastanesi</option>
<option value="Manisa Sehir Hastanesi">Manisa Şehir Hastanesi</option>
<option value="Mardin Sehir Hastanesi">Mardin Şehir Hastanesi</option>
<option value="Mersin Sehir Hastanesi">Mersin Şehir Hastanesi</option>
<option value="Mugla Sehir Hastanesi">Muğla Şehir Hastanesi</option>
<option value="Mus Sehir Hastanesi">Muş Şehir Hastanesi</option>
<option value="Nevsehir Sehir Hastanesi">Nevşehir Şehir Hastanesi</option>
<option value="Nigde Sehir Hastanesi">Niğde Şehir Hastanesi</option>
<option value="Ordu Sehir Hastanesi">Ordu Şehir Hastanesi</option>
<option value="Osmaniye Sehir Hastanesi">Osmaniye Şehir Hastanesi</option>
<option value="Rize Sehir Hastanesi">Rize Şehir Hastanesi</option>
<option value="Sakarya Sehir Hastanesi">Sakarya Şehir Hastanesi</option>
<option value="Samsun Sehir Hastanesi">Samsun Şehir Hastanesi</option>
<option value="Siirt Sehir Hastanesi">Siirt Şehir Hastanesi</option>
<option value="Sinop Sehir Hastanesi">Sinop Şehir Hastanesi</option>
<option value="Sivas Sehir Hastanesi">Sivas Şehir Hastanesi</option>
<option value="Sanliurfa Sehir Hastanesi">Şanlıurfa Şehir Hastanesi</option>
<option value="Sirnak Sehir Hastanesi">Şırnak Şehir Hastanesi</option>
<option value="Tekirdag Sehir Hastanesi">Tekirdağ Şehir Hastanesi</option>
<option value="Tokat Sehir Hastanesi">Tokat Şehir Hastanesi</option>
<option value="Trabzon Sehir Hastanesi">Trabzon Şehir Hastanesi</option>
<option value="Tunceli Sehir Hastanesi">Tunceli Şehir Hastanesi</option>
<option value="Usak Sehir Hastanesi">Uşak Şehir Hastanesi</option>
<option value="Van Sehir Hastanesi">Van Şehir Hastanesi</option>
<option value="Yalova Sehir Hastanesi">Yalova Şehir Hastanesi</option>
<option value="Yozgat Sehir Hastanesi">Yozgat Şehir Hastanesi</option>
<option value="Zonguldak Sehir Hastanesi">Zonguldak Şehir Hastanesi</option>

        </select>
        <select name="klinik" class="klinik" required>
        <option value="Kadın Doğum Klinği">Kadın Doğum Klinği</option>
<option value="Kardiyoloji Klinği">Kardiyoloji Klinği</option>
<option value="Ortopedi Klinği">Ortopedi Klinği</option>
<option value="Nöroloji Klinği">Nöroloji Klinği</option>
<option value="Dermatoloji Klinği">Dermatoloji Klinği</option>
<option value="Psikiyatri Klinği">Psikiyatri Klinği</option>
<option value="Gastroenteroloji Klinği">Gastroenteroloji Klinği</option>
<option value="Üroloji Klinği">Üroloji Klinği</option>
<option value="İç Hastalıkları Klinği">İç Hastalıkları Klinği</option>
<option value="Göz Hastalıkları Klinği">Göz Hastalıkları Klinği</option>
<option value="Kulak Burun Boğaz Klinği">Kulak Burun Boğaz Klinği</option>
<option value="Alerji Klinği">Alerji Klinği</option>
<option value="Çocuk Sağlığı Klinği">Çocuk Sağlığı Klinği</option>
<option value="Endokrinoloji Klinği">Endokrinoloji Klinği</option>
<option value="Genel Cerrahi Klinği">Genel Cerrahi Klinği</option>
<option value="Fiziksel Tıp ve Rehabilitasyon Klinği">Fiziksel Tıp ve Rehabilitasyon Klinği</option>
<option value="Hematoloji Klinği">Hematoloji Klinği</option>
<option value="Beyin Cerrahisi Klinği">Beyin Cerrahisi Klinği</option>
<option value="Cilt Bakımı Klinği">Cilt Bakımı Klinği</option>
<option value="Ebeveyn Danışmanlık Klinği">Ebeveyn Danışmanlık Klinği</option>
<option value="Pediatri Klinği">Pediatri Klinği</option>
<option value="Radyoloji Klinği">Radyoloji Klinği</option>
<option value="Dahiliye Klinği">Dahiliye Klinği</option>
<option value="Mikrobiyoloji Klinği">Mikrobiyoloji Klinği</option>
<option value="Anestezi ve Reanimasyon Klinği">Anestezi ve Reanimasyon Klinği</option>
<option value="Tıbbi Onkoloji Klinği">Tıbbi Onkoloji Klinği</option>
<option value="Ağız ve Diş Sağlığı Klinği">Ağız ve Diş Sağlığı Klinği</option>
<option value="Biyokimya Klinği">Biyokimya Klinği</option>
<option value="İmmünoloji Klinği">İmmünoloji Klinği</option>
<option value="Nefroloji Klinği">Nefroloji Klinği</option>
<option value="Palyatif Bakım Klinği">Palyatif Bakım Klinği</option>
<option value="Rehabilitasyon Klinği">Rehabilitasyon Klinği</option>
<option value="Beyin ve Sinir Cerrahisi Klinği">Beyin ve Sinir Cerrahisi Klinği</option>
<option value="Fizyoterapi Klinği">Fizyoterapi Klinği</option>
<option value="Plastik Cerrahi Klinği">Plastik Cerrahi Klinği</option>
<option value="Spor Hekimliği Klinği">Spor Hekimliği Klinği</option>
<option value="Evde Sağlık Hizmetleri Klinği">Evde Sağlık Hizmetleri Klinği</option>
        </select>
        <select name="doktor" class="doktor" required>
        <option value="Ahmet Yılmaz">Ahmet Yılmaz</option>
<option value="Ayşe Demir">Ayşe Demir</option>
<option value="Mehmet Kaya">Mehmet Kaya</option>
<option value="Zeynep Öztürk">Zeynep Öztürk</option>
<option value="Ali Vural">Ali Vural</option>
<option value="Elif Arslan">Elif Arslan</option>
<option value="Burak Tekin">Burak Tekin</option>
<option value="Hakan Çelik">Hakan Çelik</option>
<option value="Selin Can">Selin Can</option>
<option value="Murat Aydın">Murat Aydın</option>
<option value="Buse Korkmaz">Buse Korkmaz</option>
<option value="Ferhat Yıldırım">Ferhat Yıldırım</option>
<option value="Emine Kara">Emine Kara</option>
<option value="Okan Demirtaş">Okan Demirtaş</option>
<option value="Sedef Çetin">Sedef Çetin</option>
<option value="Cemre Şahin">Cemre Şahin</option>
<option value="Kemal Polat">Kemal Polat</option>
<option value="Aylin Yalçın">Aylin Yalçın</option>
<option value="Onur Eren">Onur Eren</option>
<option value="Derya Çakır">Derya Çakır</option>
<option value="Gökhan Tuncer">Gökhan Tuncer</option>
<option value="Yasemin Koç">Yasemin Koç</option>
<option value="Zekiye Kara">Zekiye Kara</option>
<option value="Canan Özdemir">Canan Özdemir</option>
<option value="Serdar Öztürk">Serdar Öztürk</option>
<option value="Meryem Toprak">Meryem Toprak</option>
<option value="Ercan Yılmaz">Ercan Yılmaz</option>
<option value="Hüseyin Peker">Hüseyin Peker</option>
<option value="İrem Cengiz">İrem Cengiz</option>
<option value="Sinan Doğan">Sinan Doğan</option>
<option value="Özgür Bayram">Özgür Bayram</option>
<option value="Sibel Aydın">Sibel Aydın</option>
<option value="Levent Bozkurt">Levent Bozkurt</option>
<option value="Nurcan Akbaş">Nurcan Akbaş</option>
<option value="Volkan Avcı">Volkan Avcı</option>
<option value="Burcu Yılmaz">Burcu Yılmaz</option>
<option value="Dursun Kaya">Dursun Kaya</option>
<option value="Fadime Kılıç">Fadime Kılıç</option>
<option value="Neşe Acar">Neşe Acar</option>
<option value="Cengiz Altun">Cengiz Altun</option>
        </select>
        <button type="submit" name="randevu_kayıt">Randevuyu Kaydet</button>
        
        </form>
        
    </div>
    <div class="orta_div" id="ailehekimi_div">
        <h3>Aile Hekimi</h3>
        <p>
            Aile Hekiminize Ait Bir Çalışma Saati Bulunmamıştır.
        </p>
    </div>
</body>
</html>