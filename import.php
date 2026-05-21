<?php
// i:/My Drive/PARENTAL CONTROL/web/import.php
// Script otomatis untuk mengimpor tabel database FamilySync ke MySQL Railway

require_once 'db.php';

try {
    // Membaca file SQL
    $sqlFile = 'signaling.sql';
    if (!file_exists($sqlFile)) {
        die("File $sqlFile tidak ditemukan di server!");
    }

    $sql = file_get_contents($sqlFile);

    // Mengeksekusi seluruh query pembuatan tabel
    $pdo->exec($sql);

    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h2 style='color: #10b981;'>🎉 Database Berhasil Di-import!</h2>";
    echo "<p style='color: #4b5563;'>Semua tabel (devices, notifications, dan signaling) telah sukses dibuat secara otomatis di MySQL Railway Anda.</p>";
    echo "<a href='index.php' style='display: inline-block; background: #0284c7; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; margin-top: 15px; font-weight: bold;'>Buka Web Dashboard</a>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h2 style='color: #ef4444;'>❌ Impor Database Gagal!</h2>";
    echo "<p style='color: #4b5563;'>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
