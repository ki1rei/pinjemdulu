<?php
require __DIR__ . '/includes/koneksi.php';

echo '<h3>Koneksi berhasil!</h3>';

$tabel = ['organisasi', 'kategori', 'pengguna', 'inventaris', 'peminjaman', 'request_pj'];

echo '<table border="1" cellpadding="6">';
echo '<tr><th>Tabel</th><th>Jumlah baris</th></tr>';
foreach ($tabel as $t) {
    $jumlah = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
    echo "<tr><td>$t</td><td>$jumlah</td></tr>";
}
echo '</table>';