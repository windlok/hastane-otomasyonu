<?php 
include 'header.php';
oturum_kontrol();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Hastane Otomasyonu - Randevu Al</title>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="page-container">
        <div class="page-header">
            <h2>Hızlı Randevu Sistemi</h2>
            <p>Aşağıdaki alanları doldurarak dilediğiniz tarihe randevu alabilirsiniz.</p>
        </div>

        <?php 
        if (isset($_GET['durum'])) {
            mesaj_goster($_GET['durum']);
        }
        ?>

        <div class="dashboard-grid">
            <!-- Randevu Alma Formu -->
            <div class="card">
                <h3 class="card-title">
                    <span class="card-icon">📋</span> Randevu Bilgileri
                </h3>
                <form action="islem.php" method="post">
                    <?php echo csrf_input(); ?>
                    
                    <div class="form-group">
                        <label for="tarih">Randevu Tarihi</label>
                        <input type="date" id="tarih" name="tarih" class="date-control" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="sehirler">Şehir</label>
                        <select id="sehirler" name="sehirler" class="select-control" required>
                            <option value="">Seçiniz...</option>
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
                    </div>

                    <div class="form-group">
                        <label for="hastane">Hastane</label>
                        <select id="hastane" name="hastane" class="select-control" required>
                            <option value="">Seçiniz...</option>
                            <option value="Ankara Şehir Hastanesi">Ankara Şehir Hastanesi</option>
                            <option value="İstanbul Şehir Hastanesi">İstanbul Şehir Hastanesi</option>
                            <option value="İzmir Şehir Hastanesi">İzmir Şehir Hastanesi</option>
                            <option value="Adana Şehir Hastanesi">Adana Şehir Hastanesi</option>
                            <option value="Bursa Şehir Hastanesi">Bursa Şehir Hastanesi</option>
                            <option value="Antalya Şehir Hastanesi">Antalya Şehir Hastanesi</option>
                            <option value="Eskişehir Devlet Hastanesi">Eskişehir Devlet Hastanesi</option>
                            <option value="Kocaeli Şehir Hastanesi">Kocaeli Şehir Hastanesi</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="klinik">Klinik</label>
                        <select id="klinik" name="klinik" class="select-control" required>
                            <option value="">Seçiniz...</option>
                            <option value="Kadın Doğum Kliniği">Kadın Doğum Kliniği</option>
                            <option value="Kardiyoloji Kliniği">Kardiyoloji Kliniği</option>
                            <option value="Ortopedi Kliniği">Ortopedi Kliniği</option>
                            <option value="Nöroloji Kliniği">Nöroloji Kliniği</option>
                            <option value="Dermatoloji Kliniği">Dermatoloji Kliniği</option>
                            <option value="Psikiyatri Kliniği">Psikiyatri Kliniği</option>
                            <option value="Gastroenteroloji Kliniği">Gastroenteroloji Kliniği</option>
                            <option value="Üroloji Kliniği">Üroloji Kliniği</option>
                            <option value="İç Hastalıkları Kliniği">İç Hastalıkları Kliniği</option>
                            <option value="Göz Hastalıkları Kliniği">Göz Hastalıkları Kliniği</option>
                            <option value="Kulak Burun Boğaz Kliniği">Kulak Burun Boğaz Kliniği</option>
                            <option value="Alerji Kliniği">Alerji Kliniği</option>
                            <option value="Çocuk Sağlığı Kliniği">Çocuk Sağlığı Kliniği</option>
                            <option value="Endokrinoloji Kliniği">Endokrinoloji Kliniği</option>
                            <option value="Genel Cerrahi Kliniği">Genel Cerrahi Kliniği</option>
                            <option value="Fiziksel Tıp ve Rehabilitasyon Kliniği">Fiziksel Tıp ve Rehabilitasyon Kliniği</option>
                            <option value="Hematoloji Kliniği">Hematoloji Kliniği</option>
                            <option value="Beyin Cerrahisi Kliniği">Beyin Cerrahisi Kliniği</option>
                            <option value="Ağız ve Diş Sağlığı Kliniği">Ağız ve Diş Sağlığı Kliniği</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="doktor">Doktor</label>
                        <select id="doktor" name="doktor" class="select-control" required>
                            <option value="">Seçiniz...</option>
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
                        </select>
                    </div>

                    <button type="submit" name="randevu_kayıt" class="btn btn-primary">Randevuyu Kaydet</button>
                </form>
            </div>

            <!-- Aile Hekimi & Bilgilendirme -->
            <div>
                <div class="card">
                    <h3 class="card-title">
                        <span class="card-icon">👨‍⚕️</span> Aile Hekimi
                    </h3>
                    <div class="info-card-body" style="text-align: center; padding: 20px 0;">
                        <div style="font-size: 40px; margin-bottom: 10px;">🩺</div>
                        <p style="font-weight: 600; font-size: 16px; margin-bottom: 5px;">Dr. Kemal Polat</p>
                        <p style="color: var(--text-light); font-size: 14px; margin-bottom: 15px;">Kayıtlı Aile Hekiminiz</p>
                        <div class="alert alert-warning" style="margin-bottom: 0; display: inline-flex; justify-content: center; width: 100%;">
                            Aile Hekiminize Ait Bir Çalışma Saati Bulunmamaktadır.
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3 class="card-title">
                        <span class="card-icon">💡</span> Önemli Bilgilendirme
                    </h3>
                    <ul style="padding-left: 20px; font-size: 14px; color: var(--text-light);">
                        <li style="margin-bottom: 8px;">Randevunuza giderken TC Kimlik kartınızı yanınızda bulundurmayı unutmayınız.</li>
                        <li style="margin-bottom: 8px;">Randevunuzu iptal etmek veya değiştirmek isterseniz, Randevularım sayfasından son 15 dakikaya kadar silebilirsiniz.</li>
                        <li style="margin-bottom: 8px;">Geçmiş tarihlere randevu kaydı oluşturulamaz.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>