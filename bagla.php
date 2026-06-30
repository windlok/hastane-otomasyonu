<?php 

try {
    $db = new PDO(
        'mysql:host=127.0.0.1;port=3306;dbname=hastane_otomasyonu;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    //echo 'veritabanına bağlantı başarılı';
} catch (Exception $e) {
    echo $e->getMessage();
}

?>