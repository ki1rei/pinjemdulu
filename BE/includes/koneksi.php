<?php
// Baca file .env sederhana
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $baris) {
        if (str_starts_with(trim($baris), '#') || !str_contains($baris, '=')) continue;
        [$k, $v] = explode('=', $baris, 2);
        $_ENV[trim($k)] = trim($v);
    }
}

$dsn = sprintf(
    'pgsql:host=%s;port=%s;dbname=%s;sslmode=require',
    $_ENV['DB_HOST'], $_ENV['DB_PORT'], $_ENV['DB_NAME']
);

try {
    $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // Jangan tampilkan detail error ke pengguna di versi produksi
    die('Koneksi database gagal: ' . $e->getMessage());
}