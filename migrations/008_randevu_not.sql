CREATE TABLE IF NOT EXISTS randevu_not (
    not_id INT AUTO_INCREMENT PRIMARY KEY,
    randevu_id INT NOT NULL,
    doktor_id INT NOT NULL,
    not_metni TEXT NOT NULL,
    olusturma_tarihi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (randevu_id) REFERENCES randevu(randevu_id) ON DELETE CASCADE,
    FOREIGN KEY (doktor_id) REFERENCES doktor(doktor_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
