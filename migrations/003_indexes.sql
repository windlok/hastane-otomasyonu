-- Performans indeksleri
USE hastane_otomasyonu;

CREATE INDEX idx_kullanici_tc ON kullanici(kullanici_tc);
CREATE INDEX idx_kullanici_email ON kullanici(kullanici_email);
CREATE INDEX idx_randevu_kullanici_tarih ON randevu(kullanici_id, randevu_tarih);
CREATE INDEX idx_randevu_durum ON randevu(durum);
