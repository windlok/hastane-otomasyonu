CREATE TABLE IF NOT EXISTS doktor_calisma_saat (
    calisma_id INT AUTO_INCREMENT PRIMARY KEY,
    doktor_id INT NOT NULL,
    gun INT NOT NULL COMMENT '1=Pazartesi ... 7=Pazar',
    baslangic TIME NOT NULL DEFAULT '09:00:00',
    bitis TIME NOT NULL DEFAULT '17:00:00',
    UNIQUE KEY uq_doktor_gun (doktor_id, gun),
    FOREIGN KEY (doktor_id) REFERENCES doktor(doktor_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO doktor_calisma_saat (doktor_id, gun, baslangic, bitis)
SELECT d.doktor_id, g.gun, '09:00:00', '17:00:00'
FROM doktor d
CROSS JOIN (
    SELECT 1 AS gun UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
) g;
