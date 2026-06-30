ALTER TABLE kullanici MODIFY COLUMN rol ENUM('hasta','doktor','admin') NOT NULL DEFAULT 'hasta';

INSERT IGNORE INTO kullanici (kullanici_tc, kullanici_password, kullanici_adsoyad, kullanici_email, kullanici_telefon, rol, aktif, kayit_tarihi)
VALUES ('00000000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'admin@hastane.com', '05550000000', 'admin', 1, NOW());
