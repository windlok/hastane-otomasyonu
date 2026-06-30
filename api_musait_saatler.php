<?php
include 'header.php';
hasta_kontrol();

header('Content-Type: application/json; charset=utf-8');

$doktor_id = isset($_GET['doktor_id']) ? intval($_GET['doktor_id']) : 0;
$tarih = isset($_GET['tarih']) ? trim($_GET['tarih']) : '';

if (!$doktor_id || !$tarih || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tarih)) {
    echo json_encode(['saatler' => [], 'hata' => 'Geçersiz parametre']);
    exit;
}

if (strtotime($tarih) < strtotime(date('Y-m-d'))) {
    echo json_encode(['saatler' => [], 'hata' => 'Geçmiş tarih']);
    exit;
}

try {
    $doktor = $db->prepare('SELECT doktor_id FROM doktor WHERE doktor_id = ?');
    $doktor->execute([$doktor_id]);
    if (!$doktor->fetch()) {
        echo json_encode(['saatler' => [], 'hata' => 'Doktor bulunamadı']);
        exit;
    }

    echo json_encode(['saatler' => musait_saatler($db, $doktor_id, $tarih)]);
} catch (PDOException $e) {
    echo json_encode(['saatler' => [], 'hata' => 'Sistem hatası']);
}
