<?php
// i:/My Drive/PARENTAL CONTROL/web/db.php
// Konfigurasi Database untuk Localhost / InfinityFree / Railway

// Cek apakah berjalan di Railway (Railway menyuntikkan environment variables secara otomatis)
if (getenv('MYSQLHOST') !== false) {
    define('DB_HOST', getenv('MYSQLHOST') . ':' . getenv('MYSQLPORT'));
    define('DB_USER', getenv('MYSQLUSER'));
    define('DB_PASS', getenv('MYSQLPASSWORD'));
    define('DB_NAME', getenv('MYSQLDATABASE'));
} else {
    // Konfigurasi Database untuk Localhost
    // define('DB_HOST', 'localhost');
    // define('DB_USER', 'root');
    // define('DB_PASS', '');
    // define('DB_NAME', 'familysync');

    // Konfigurasi Database untuk InfinityFree (Default Manual)
    define('DB_HOST', 'sql200.infinityfree.com');
    define('DB_USER', 'if0_41982465');
    define('DB_PASS', 'jzEk5BBTgYVwXDI');
    define('DB_NAME', 'if0_41982465_familysync');
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // Return JSON error if API request
    if (strpos($_SERVER['REQUEST_URI'], 'api.php') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
        exit;
    }
    // Simple direct error for Web Dashboard
    die("Koneksi Database Gagal! Harap pastikan database sudah di-import sesuai dengan 'signaling.sql'. Error: " . $e->getMessage());
}
?>
