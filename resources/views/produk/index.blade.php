<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Produk UMKM</title>
</head>
<body>
    <h1>Data Produk UMKM</h1>

    @if (session('sukses'))
        <p><strong>{{ session('sukses') }}</strong></p>
    @endif

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    @endif

    <h2>Tambah Produk</h2>
    <form method="POST" action="{{ route('produk.store') }}">
        @csrf
        <p>Kode Produk: <input type="text" name="kode_produk" value="{{ old('kode_produk') }}"></p>
        <p>Nama Produk: <input type="text" name="nama_produk" value="{{ old('nama_produk') }}"></p>
        <p>SKU: <input type="text" name="sku" value="{{ old('sku') }}"></p>
        <p>Stok: <input type="text" name="stok" value="{{ old('stok') }}"></p>
        <p>Kategori:
            <select name="kategori_produk_id">
                <option value="">-- Pilih Kategori --</option>
                @foreach ($kategori as $k)
                    <option value="{{ $k['id'] }}" @selected(old('kategori_produk_id') == $k['id'])>{{ $k['nama_kategori'] }}</option>
                @endforeach
            </select>
        </p>
        <button type="submit">Simpan</button>
    </form>

    <h2>Cari dan Filter</h2>
    <form method="GET" action="{{ route('produk.index') }}">
        Kata kunci: <input type="text" name="q" value="{{ $q }}">
        Kategori:
        <select name="kategori">
            <option value="">Semua</option>
            @foreach ($kategori as $k)
                <option value="{{ $k['id'] }}" @selected($kat == $k['id'])>{{ $k['nama_kategori'] }}</option>
            @endforeach
        </select>
        <button type="submit">Terapkan</button>
        <a href="{{ route('produk.index') }}">Reset</a>
    </form>

    <h2>Daftar Produk</h2>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>No</th><th>Kode</th><th>Nama</th><th>SKU</th>
            <th>Kategori</th><th>Stok</th><th>Status</th><th>Aksi</th>
        </tr>
        @forelse ($produk as $i => $p)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p['kode_produk'] }}</td>
                <td>{{ $p['nama_produk'] }}</td>
                <td>{{ $p['sku'] }}</td>
                <td>{{ $p['nama_kategori'] }}</td>
                <td>{{ $p['stok'] }}</td>
                <td>
                    @if (statusStok((int) $p['stok']) === 'Tersedia')
                        Tersedia
                    @else
                        <strong>Habis</strong>
                    @endif
                </td>
                <td>
                    <a href="{{ route('produk.edit', $p['id']) }}">Edit</a>
                    <form method="POST" action="{{ route('produk.destroy', $p['id']) }}" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8">Data tidak ditemukan.</td></tr>
        @endforelse
    </table>
</body>
</html>
