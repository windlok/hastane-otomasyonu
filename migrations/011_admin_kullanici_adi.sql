ALTER TABLE kullanici ADD COLUMN kullanici_adi VARCHAR(50) DEFAULT NULL UNIQUE AFTER kullanici_tc;
UPDATE kullanici SET kullanici_adi = 'admin' WHERE rol = 'admin' AND kullanici_adi IS NULL;
