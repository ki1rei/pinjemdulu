<?php
// Handler /api/organisasi (baca saja). Gambar icon (bytea) tidak ikut di JSON;
// kolom punyaIcon hanya penanda ada/tidaknya.

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Method tidak didukung.', 'METHOD_NOT_ALLOWED', 405);
}

try {
    $rows = $pdo->query(
        'SELECT id_organisasi, nama_organisasi, (icon IS NOT NULL) AS punya_icon
           FROM organisasi
          ORDER BY nama_organisasi'
    )->fetchAll();
    json_out(array_map('camel_keys', $rows));
} catch (PDOException $e) {
    pdo_error($e);
}