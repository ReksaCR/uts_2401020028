<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

function statusStok(int $stok): string
{
    if ($stok > 0) {
        return 'Tersedia';
    }

    return 'Habis';
}

function responsAman()
{
    return response('Terjadi kesalahan pada server. Silakan coba lagi nanti.', 500);
}

function daftarKategori(PDO $pdo): array
{
    return $pdo->query('SELECT id, nama_kategori FROM kategori_produk ORDER BY nama_kategori')
        ->fetchAll(PDO::FETCH_ASSOC);
}

function bersihkanInput(Request $r): array
{
    $ambil = fn ($k) => trim(strip_tags((string) $r->input($k)));

    return [
        'kode_produk'        => $ambil('kode_produk'),
        'nama_produk'        => $ambil('nama_produk'),
        'sku'                => $ambil('sku'),
        'stok'               => $ambil('stok'),
        'kategori_produk_id' => $ambil('kategori_produk_id'),
    ];
}

function validasiProduk(PDO $pdo, array $d, int $abaikanId = 0): array
{
    $e = [];

    if (!preg_match('/^[A-Za-z0-9]{10}$/', $d['kode_produk'])) {
        $e['kode_produk'] = 'Kode produk harus tepat 10 karakter alfanumerik.';
    }

    $len = mb_strlen($d['nama_produk']);
    if ($len < 3 || $len > 100) {
        $e['nama_produk'] = 'Nama produk harus 3-100 karakter.';
    }

    $len = mb_strlen($d['sku']);
    if ($len < 5 || $len > 30) {
        $e['sku'] = 'SKU harus 5-30 karakter.';
    }

    if (!preg_match('/^\d{1,3}$/', $d['stok']) || (int) $d['stok'] > 500) {
        $e['stok'] = 'Stok harus bilangan bulat 0-500.';
    }

    if (!preg_match('/^\d+$/', $d['kategori_produk_id'])) {
        $e['kategori_produk_id'] = 'Kategori wajib dipilih.';
    } else {
        $st = $pdo->prepare('SELECT id FROM kategori_produk WHERE id = :id');
        $st->execute([':id' => $d['kategori_produk_id']]);
        if (!$st->fetch()) {
            $e['kategori_produk_id'] = 'Kategori tidak tersedia.';
        }
    }

    if (!isset($e['kode_produk'])) {
        $st = $pdo->prepare('SELECT id FROM produk WHERE kode_produk = :v AND id <> :id');
        $st->execute([':v' => $d['kode_produk'], ':id' => $abaikanId]);
        if ($st->fetch()) {
            $e['kode_produk'] = 'Kode produk sudah digunakan.';
        }
    }

    if (!isset($e['sku'])) {
        $st = $pdo->prepare('SELECT id FROM produk WHERE sku = :v AND id <> :id');
        $st->execute([':v' => $d['sku'], ':id' => $abaikanId]);
        if ($st->fetch()) {
            $e['sku'] = 'SKU sudah digunakan.';
        }
    }

    return $e;
}


Route::get('/', fn () => redirect('/produk'));

Route::get('/produk', function (Request $request) {
    $q   = trim(strip_tags((string) $request->query('q')));
    $kat = trim((string) $request->query('kategori'));

    try {
        $pdo      = DB::connection()->getPdo();
        $kategori = daftarKategori($pdo);

        $sql = 'SELECT p.id, p.kode_produk, p.nama_produk, p.sku, p.stok, k.nama_kategori
                FROM produk p
                JOIN kategori_produk k ON k.id = p.kategori_produk_id
                WHERE 1 = 1';
        $par = [];

        if ($q !== '') {
            $sql .= ' AND (p.kode_produk LIKE :q1 OR p.nama_produk LIKE :q2 OR p.sku LIKE :q3)';
            $like = '%' . $q . '%';
            $par[':q1'] = $like;
            $par[':q2'] = $like;
            $par[':q3'] = $like;
        }

        if (preg_match('/^\d+$/', $kat)) {
            $sql .= ' AND p.kategori_produk_id = :kat';
            $par[':kat'] = $kat;
        }

        $sql .= ' ORDER BY p.id';

        $st = $pdo->prepare($sql);
        $st->execute($par);
        $produk = $st->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        report($e);

        return responsAman();
    }

    return view('produk.index', compact('produk', 'kategori', 'q', 'kat'));
})->name('produk.index');

Route::post('/produk', function (Request $request) {
    $d = bersihkanInput($request);

    try {
        $pdo = DB::connection()->getPdo();
        $err = validasiProduk($pdo, $d);
        if ($err) {
            return redirect()->route('produk.index')->withErrors($err)->withInput($d);
        }

        $st = $pdo->prepare(
            'INSERT INTO produk (kode_produk, nama_produk, sku, stok, kategori_produk_id, created_at, updated_at)
             VALUES (:kode, :nama, :sku, :stok, :kat, NOW(), NOW())'
        );
        $st->execute([
            ':kode' => $d['kode_produk'],
            ':nama' => $d['nama_produk'],
            ':sku'  => $d['sku'],
            ':stok' => $d['stok'],
            ':kat'  => $d['kategori_produk_id'],
        ]);
    } catch (Throwable $e) {
        report($e);
        $pesan = ($e instanceof PDOException && (string) $e->getCode() === '23000')
            ? 'Data ditolak: kode/SKU duplikat atau kategori tidak valid.'
            : 'Terjadi kesalahan saat menyimpan data.';

        return redirect()->route('produk.index')->withErrors(['umum' => $pesan])->withInput($d);
    }

    return redirect()->route('produk.index')->with('sukses', 'Produk berhasil ditambahkan.');
})->name('produk.store');

Route::get('/produk/{id}/edit', function (int $id) {
    try {
        $pdo = DB::connection()->getPdo();
        $st  = $pdo->prepare(
            'SELECT id, kode_produk, nama_produk, sku, stok, kategori_produk_id FROM produk WHERE id = :id'
        );
        $st->execute([':id' => $id]);
        $p        = $st->fetch(PDO::FETCH_ASSOC);
        $kategori = daftarKategori($pdo);
    } catch (Throwable $e) {
        report($e);

        return responsAman();
    }

    if (!$p) {
        return redirect()->route('produk.index')->withErrors(['umum' => 'Produk tidak ditemukan.']);
    }

    return view('produk.edit', compact('p', 'kategori'));
})->whereNumber('id')->name('produk.edit');

Route::put('/produk/{id}', function (Request $request, int $id) {
    $d = bersihkanInput($request);

    try {
        $pdo = DB::connection()->getPdo();
        $err = validasiProduk($pdo, $d, $id);
        if ($err) {
            return redirect()->route('produk.edit', $id)->withErrors($err)->withInput($d);
        }

        $st = $pdo->prepare(
            'UPDATE produk
             SET kode_produk = :kode, nama_produk = :nama, sku = :sku, stok = :stok,
                 kategori_produk_id = :kat, updated_at = NOW()
             WHERE id = :id'
        );
        $st->execute([
            ':kode' => $d['kode_produk'],
            ':nama' => $d['nama_produk'],
            ':sku'  => $d['sku'],
            ':stok' => $d['stok'],
            ':kat'  => $d['kategori_produk_id'],
            ':id'   => $id,
        ]);
    } catch (Throwable $e) {
        report($e);

        return redirect()->route('produk.edit', $id)
            ->withErrors(['umum' => 'Terjadi kesalahan saat memperbarui data.'])->withInput($d);
    }

    return redirect()->route('produk.index')->with('sukses', 'Produk berhasil diperbarui.');
})->whereNumber('id')->name('produk.update');

Route::delete('/produk/{id}', function (int $id) {
    try {
        $st = DB::connection()->getPdo()->prepare('DELETE FROM produk WHERE id = :id');
        $st->execute([':id' => $id]);
    } catch (Throwable $e) {
        report($e);

        return redirect()->route('produk.index')
            ->withErrors(['umum' => 'Terjadi kesalahan saat menghapus data.']);
    }

    return redirect()->route('produk.index')->with('sukses', 'Produk berhasil dihapus.');
})->whereNumber('id')->name('produk.destroy');
