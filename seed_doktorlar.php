<?php
/**
 * Örnek doktor hesapları oluşturur (migrate.php tarafından çağrılır)
 * Tüm demo doktor şifresi: doktor123
 */

$doktorlar = [
    ['tc' => '11111111110', 'ad' => 'Dr. Ahmet Yılmaz', 'klinik' => 'Kardiyoloji Kliniği', 'hastane' => 'Ankara Şehir Hastanesi', 'sehir' => 'Ankara'],
    ['tc' => '22222222220', 'ad' => 'Dr. Ayşe Demir', 'klinik' => 'Kadın Doğum Kliniği', 'hastane' => 'İstanbul Şehir Hastanesi', 'sehir' => 'İstanbul'],
    ['tc' => '33333333330', 'ad' => 'Dr. Mehmet Kaya', 'klinik' => 'Ortopedi Kliniği', 'hastane' => 'İzmir Şehir Hastanesi', 'sehir' => 'İzmir'],
    ['tc' => '44444444440', 'ad' => 'Dr. Zeynep Öztürk', 'klinik' => 'Nöroloji Kliniği', 'hastane' => 'Bursa Şehir Hastanesi', 'sehir' => 'Bursa'],
];

$sifre = password_hash('doktor123', PASSWORD_DEFAULT);

foreach ($doktorlar as $d) {
    $kontrol = $db->prepare('SELECT kullanici_id FROM kullanici WHERE kullanici_tc = ?');
    $kontrol->execute([$d['tc']]);
    if ($kontrol->fetch()) {
        continue;
    }

    $ekle = $db->prepare('INSERT INTO kullanici (kullanici_tc, kullanici_adsoyad, kullanici_password, rol) VALUES (?, ?, ?, ?)');
    $ekle->execute([$d['tc'], $d['ad'], $sifre, 'doktor']);
    $kullanici_id = $db->lastInsertId();

    $doktor_ekle = $db->prepare('INSERT INTO doktor (kullanici_id, klinik, hastane, sehir) VALUES (?, ?, ?, ?)');
    $doktor_ekle->execute([$kullanici_id, $d['klinik'], $d['hastane'], $d['sehir']]);
}

echo "✓ Örnek doktor hesapları oluşturuldu (şifre: doktor123)\n";
