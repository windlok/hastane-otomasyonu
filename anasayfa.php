<?php 
include 'header.php';
hasta_kontrol();

try {
    $doktorlar = $db->query('SELECT d.doktor_id, d.klinik, d.hastane, d.sehir, k.kullanici_adsoyad
        FROM doktor d
        JOIN kullanici k ON d.kullanici_id = k.kullanici_id
        WHERE k.aktif = 1
        ORDER BY k.kullanici_adsoyad')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $doktorlar = [];
}
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
            <p>Doktor, tarih ve saat seçerek randevunuzu oluşturun. Dolu saatler otomatik olarak gizlenir.</p>
        </div>

        <?php 
        if (isset($_GET['durum'])) {
            mesaj_goster($_GET['durum']);
        }
        ?>

        <div class="dashboard-grid">
            <div class="card">
                <h3 class="card-title">
                    <span class="card-icon">📋</span> Randevu Bilgileri
                </h3>
                <form action="islem.php" method="post" id="randevuForm">
                    <?php echo csrf_input(); ?>

                    <div class="form-group">
                        <label for="doktor_id">Doktor Seçin</label>
                        <select id="doktor_id" name="doktor_id" class="select-control" required>
                            <option value="">Seçiniz...</option>
                            <?php foreach ($doktorlar as $d): ?>
                            <option value="<?php echo (int) $d['doktor_id']; ?>"
                                data-sehir="<?php echo htmlspecialchars($d['sehir']); ?>"
                                data-hastane="<?php echo htmlspecialchars($d['hastane']); ?>"
                                data-klinik="<?php echo htmlspecialchars($d['klinik']); ?>">
                                <?php echo htmlspecialchars($d['kullanici_adsoyad']); ?> — <?php echo htmlspecialchars($d['klinik']); ?> (<?php echo htmlspecialchars($d['sehir']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="doktorBilgi" class="doktor-info-box" style="display:none;">
                        <p><strong>Şehir:</strong> <span id="infoSehir">-</span></p>
                        <p><strong>Hastane:</strong> <span id="infoHastane">-</span></p>
                        <p><strong>Klinik:</strong> <span id="infoKlinik">-</span></p>
                    </div>

                    <div class="form-group">
                        <label for="tarih">Randevu Tarihi</label>
                        <input type="date" id="tarih" name="tarih" class="date-control" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="saat">Randevu Saati</label>
                        <select id="saat" name="saat" class="select-control" required disabled>
                            <option value="">Önce doktor ve tarih seçin</option>
                        </select>
                        <small id="saatUyari" class="form-hint"></small>
                    </div>

                    <button type="submit" name="randevu_kayıt" class="btn btn-primary">Randevuyu Kaydet</button>
                </form>
            </div>

            <div>
                <div class="card">
                    <h3 class="card-title">
                        <span class="card-icon">💡</span> Önemli Bilgilendirme
                    </h3>
                    <ul class="info-list">
                        <li>Randevu saatleri 09:00 – 16:30 arası 30 dakikalık aralıklarla verilir.</li>
                        <li>Bir doktorun aynı saatine yalnızca bir hasta randevu alabilir.</li>
                        <li>Dolu saatler listede gösterilmez; başka bir saat seçmelisiniz.</li>
                        <li>Randevunuzu iptal etmek için Randevularım sayfasını kullanın.</li>
                    </ul>
                </div>

                <div class="card">
                    <h3 class="card-title">
                        <span class="card-icon">🕐</span> Çalışma Saatleri
                    </h3>
                    <div class="slot-preview">
                        <?php foreach (tum_saatler() as $s): ?>
                        <span class="slot-badge"><?php echo $s; ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    const doktorSelect = document.getElementById('doktor_id');
    const tarihInput = document.getElementById('tarih');
    const saatSelect = document.getElementById('saat');
    const saatUyari = document.getElementById('saatUyari');
    const doktorBilgi = document.getElementById('doktorBilgi');

    function doktorBilgiGuncelle() {
        const opt = doktorSelect.options[doktorSelect.selectedIndex];
        if (!doktorSelect.value) {
            doktorBilgi.style.display = 'none';
            return;
        }
        document.getElementById('infoSehir').textContent = opt.dataset.sehir;
        document.getElementById('infoHastane').textContent = opt.dataset.hastane;
        document.getElementById('infoKlinik').textContent = opt.dataset.klinik;
        doktorBilgi.style.display = 'block';
    }

    function saatleriYukle() {
        const doktorId = doktorSelect.value;
        const tarih = tarihInput.value;

        saatSelect.innerHTML = '<option value="">Yükleniyor...</option>';
        saatSelect.disabled = true;
        saatUyari.textContent = '';

        if (!doktorId || !tarih) {
            saatSelect.innerHTML = '<option value="">Önce doktor ve tarih seçin</option>';
            return;
        }

        fetch('api_musait_saatler.php?doktor_id=' + doktorId + '&tarih=' + tarih)
            .then(r => r.json())
            .then(data => {
                saatSelect.innerHTML = '';
                if (!data.saatler || data.saatler.length === 0) {
                    saatSelect.innerHTML = '<option value="">Müsait saat yok</option>';
                    saatUyari.textContent = 'Bu tarihte seçilen doktor için boş randevu saati bulunmuyor.';
                    return;
                }
                saatSelect.innerHTML = '<option value="">Saat seçin...</option>';
                data.saatler.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s;
                    opt.textContent = s;
                    saatSelect.appendChild(opt);
                });
                saatSelect.disabled = false;
                saatUyari.textContent = data.saatler.length + ' müsait saat bulundu.';
            })
            .catch(() => {
                saatSelect.innerHTML = '<option value="">Hata oluştu</option>';
                saatUyari.textContent = 'Saatler yüklenemedi, sayfayı yenileyin.';
            });
    }

    doktorSelect.addEventListener('change', () => { doktorBilgiGuncelle(); saatleriYukle(); });
    tarihInput.addEventListener('change', saatleriYukle);
    </script>
</body>
</html>
