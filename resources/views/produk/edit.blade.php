<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk</title>
</head>
<body>
    <h1>Edit Produk</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('produk.update', $p['id']) }}">
        @csrf
        @method('PUT')
        <p>Kode Produk: <input type="text" name="kode_produk" value="{{ old('kode_produk', $p['kode_produk']) }}"></p>
        <p>Nama Produk: <input type="text" name="nama_produk" value="{{ old('nama_produk', $p['nama_produk']) }}"></p>
        <p>SKU: <input type="text" name="sku" value="{{ old('sku', $p['sku']) }}"></p>
        <p>Stok: <input type="text" name="stok" value="{{ old('stok', $p['stok']) }}"></p>
        <p>Kategori:
            <select name="kategori_produk_id">
                <option value="">-- Pilih Kategori --</option>
                @foreach ($kategori as $k)
                    <option value="{{ $k['id'] }}" @selected(old('kategori_produk_id', $p['kategori_produk_id']) == $k['id'])>{{ $k['nama_kategori'] }}</option>
                @endforeach
            </select>
        </p>
        <button type="submit">Update</button>
        <a href="{{ route('produk.index') }}">Batal</a>
    </form>
</body>
</html>
