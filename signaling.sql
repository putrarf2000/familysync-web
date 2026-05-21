-- i:/My Drive/PARENTAL CONTROL/web/signaling.sql
-- Skema Database MySQL untuk FamilySync

-- CATATAN: Untuk InfinityFree / Shared Hosting, Anda TIDAK BISA membuat database menggunakan query SQL.
-- Anda harus membuat database terlebih dahulu dari Control Panel InfinityFree (misal: if0_xxxx_db)
-- Lalu masuk ke phpMyAdmin untuk database tersebut dan impor file ini.
-- Baris di bawah ini sengaja di-comment agar tidak terjadi error "Access denied".

-- CREATE DATABASE IF NOT EXISTS `familysync` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `familysync`;

-- 1. Tabel Perangkat (Devices)
CREATE TABLE IF NOT EXISTS `devices` (
    `device_id` VARCHAR(100) NOT NULL,
    `device_name` VARCHAR(100) NOT NULL,
    `battery_level` INT DEFAULT 100,
    `is_locked` TINYINT(1) DEFAULT 0,
    `last_seen` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel Log Notifikasi (Notifications)
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `device_id` VARCHAR(100) NOT NULL,
    `app_name` VARCHAR(100) NOT NULL,
    `title` VARCHAR(255) DEFAULT '',
    `message` TEXT,
    `post_time` BIGINT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`device_id`) REFERENCES `devices`(`device_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel Sinyal WebRTC (Signaling)
CREATE TABLE IF NOT EXISTS `signaling` (
    `device_id` VARCHAR(100) NOT NULL,
    `sdp_offer` LONGTEXT DEFAULT NULL,
    `sdp_answer` LONGTEXT DEFAULT NULL,
    `ice_candidates_client` LONGTEXT DEFAULT NULL, -- Format JSON Array
    `ice_candidates_dashboard` LONGTEXT DEFAULT NULL, -- Format JSON Array
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`device_id`),
    FOREIGN KEY (`device_id`) REFERENCES `devices`(`device_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
