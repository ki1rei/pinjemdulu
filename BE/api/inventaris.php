<?php
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Validasi untuk POST & PUT; mengembalikan data bersih
function validasi(array $d): array {
    $err = [];
    $nama = trim($d['nama_barang'] ?? '');
    if ($nama === '' || mb_strlen($nama) > 100) $err[] = 'nama_barang wajib diisi (maks 100 karakter)';
    if (!filter_var($d['id_kategori'] ?? null, FILTER_VALIDATE_INT)) $err[] = 'id_kategori tidak valid';
    if (!filter_var($d['id_organisasi'] ?? null, FILTER_VALIDATE_INT)) $err[] = 'id_organisasi tidak valid';
    $total = filter_var($d['total_barang'] ?? null, FILTER_VALIDATE_INT);
    if ($total === false || $total < 0) $err[] = 'total_barang harus angka >= 0';
    if (!in_array($d['kondisi'] ?? '', ['Baik', 'Rusak'], true)) $err[] = "kondisi harus 'Baik' atau 'Rusak'";
    if (mb_strlen($d['deskripsi_barang'] ?? '') > 255) $err[] = 'deskripsi_barang maks 255 karakter';
    if ($err) json_out(['error' => $err], 422);

    return [
        'nama'  => $nama,
        'kat'   => (int)$d['id_kategori'],
        'org'   => (int)$d['id_organisasi'],
        'total' => $total,
        'kond'  => $d['kondisi'],
        'desk'  => $d['deskripsi_barang'] ?? null,
    ];
}

try {
    switch ($method) {

        // READ: daftar atau satu data
        case 'GET':
            $sql = 'SELECT i.id_inventaris, i.nama_barang, i.total_barang, i.kondisi,
                           i.deskripsi_barang, i.id_kategori, k.nama_kategori,
                           i.id_organisasi, o.nama_organisasi
                    FROM inventaris i
                    JOIN kategori k ON k.id_kategori = i.id_kategori
                    JOIN organisasi o ON o.id_organisasi = i.id_organisasi';
            if ($id) {
                $stmt = $pdo->prepare("$sql WHERE i.id_inventaris = :id");
                $stmt->execute(['id' => $id]);
                $row = $stmt->fetch();
                $row ? json_out($row) : json_out(['error' => 'Data tidak ditemukan'], 404);
            }
            json_out($pdo->query("$sql ORDER BY i.nama_barang")->fetchAll());

        // CREATE
        case 'POST':
            $v = validasi(input());
            $stmt = $pdo->prepare(
                'INSERT INTO inventaris (id_kategori, id_organisasi, nama_barang, total_barang, kondisi, deskripsi_barang)
                 VALUES (:kat, :org, :nama, :total, :kond, :desk)
                 RETURNING id_inventaris'
            );
            $stmt->execute($v);
            json_out(['id_inventaris' => $stmt->fetchColumn(), 'pesan' => 'Berhasil ditambahkan'], 201);

        // UPDATE
        case 'PUT':
            if (!$id) json_out(['error' => 'Parameter id wajib'], 400);
            $v = validasi(input());
            $v['id'] = $id;
            $stmt = $pdo->prepare(
                'UPDATE inventaris
                    SET id_kategori = :kat, id_organisasi = :org, nama_barang = :nama,
                        total_barang = :total, kondisi = :kond, deskripsi_barang = :desk
                  WHERE id_inventaris = :id'
            );
            $stmt->execute($v);
            $stmt->rowCount() ? json_out(['pesan' => 'Berhasil diubah'])
                              : json_out(['error' => 'Data tidak ditemukan'], 404);

        // DELETE
        case 'DELETE':
            if (!$id) json_out(['error' => 'Parameter id wajib'], 400);
            $stmt = $pdo->prepare('DELETE FROM inventaris WHERE id_inventaris = :id');
            $stmt->execute(['id' => $id]);
            $stmt->rowCount() ? json_out(['pesan' => 'Berhasil dihapus'])
                              : json_out(['error' => 'Data tidak ditemukan'], 404);

        default:
            json_out(['error' => 'Method tidak didukung'], 405);
    }
} catch (PDOException $e) {
    // 23503 = foreign key; 23505 = duplikat; 23514 = CHECK gagal
    $kode = $e->getCode();
    if ($kode === '23503') json_out(['error' => 'Data masih dipakai tabel lain (kategori/organisasi tidak ada, atau barang pernah dipinjam)'], 409);
    if ($kode === '23514') json_out(['error' => 'Data melanggar aturan tabel'], 422);
    error_log($e->getMessage());               // detail hanya di log server
    json_out(['error' => 'Kesalahan database'], 500);
}