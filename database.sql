-- ============================================================
-- FitPanel - Veritabanı Dersi Projesi
-- Veritabanı kurulum dosyası
-- ============================================================

CREATE DATABASE IF NOT EXISTS fitpanel CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;

USE fitpanel;

-- ============================================================
-- 1. TABLO: users (Kullanıcılar)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    ad_soyad      VARCHAR(100) NOT NULL,
    kullanici_adi VARCHAR(50)  NOT NULL UNIQUE,
    email         VARCHAR(150) NOT NULL UNIQUE,
    sifre         VARCHAR(255) NOT NULL,
    rol           ENUM('admin','user') NOT NULL DEFAULT 'user',
    kayit_tarihi  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================
-- 2. TABLO: puanlar (Kullanıcı Puanları)
--    user_id, users.id alanına FOREIGN KEY ile bağlı.
--    Kullanıcı silinince puanları da silinir (CASCADE).
-- ============================================================
CREATE TABLE IF NOT EXISTS puanlar (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NOT NULL,
    kategori       ENUM('Antrenman','Beslenme','Devamlılık','Performans') NOT NULL,
    puan           TINYINT UNSIGNED NOT NULL,   -- 0-100 arası
    aciklama       VARCHAR(255) DEFAULT NULL,
    eklenme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_puan_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================
-- ÖRNEK VERİLER
-- ============================================================

-- Admin hesabı (şifre: admin123)
-- Hash: password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO users (ad_soyad, kullanici_adi, email, sifre, rol) VALUES
('Sistem Yöneticisi', 'admin', 'admin@fitpanel.com',
 '$2y$10$0Bixpss76KdpqBDktos6bey636HxWOpolgL6y9iQbipbrmbAQF58S', 'admin');

-- Normal kullanıcı (şifre: user123)
-- Hash: password_hash('user123', PASSWORD_BCRYPT)
INSERT INTO users (ad_soyad, kullanici_adi, email, sifre, rol) VALUES
('Ahmet Yılmaz', 'user', 'user@fitpanel.com',
 '$2y$10$6FbeNdQTpxTavcgpVMraWOfk9PoH/5LeGsHxXcCbxkx7v5cepvuES', 'user');

-- Örnek puanlar (Ahmet Yılmaz için)
INSERT INTO puanlar (user_id, kategori, puan, aciklama) VALUES
(2, 'Antrenman',  85, 'Pazartesi göğüs antrenmanı'),
(2, 'Beslenme',   70, 'Günlük protein hedefine ulaşıldı'),
(2, 'Devamlılık', 90, 'Bu hafta hiç gün atlamadım'),
(2, 'Performans', 78, 'Kişisel rekor kırıldı');
