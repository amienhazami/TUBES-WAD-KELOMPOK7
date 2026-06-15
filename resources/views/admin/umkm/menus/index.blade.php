@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <a href="{{ route('admin.umkms.index') }}" class="text-decoration-none text-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar UMKM
        </a>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h3 class="fw-bold mb-4">Kelola Menu: {{ $umkm->nama_umkm }}</h3>
        </div>

        @if ($errors->any())
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h6 class="fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> Terjadi Kesalahan!</h6>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        <!-- Form Tambah Menu -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-plus-circle"></i> Tambah Menu Baru</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.umkm.menus.store', $umkm->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nama Menu</label>
                            <input type="text" name="nama_menu" class="form-control @error('nama_menu') is-invalid @enderror" value="{{ old('nama_menu') }}" required>
                            @error('nama_menu')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harga (Rp)</label>
                            <input type="text" name="harga" class="form-control @error('harga') is-invalid @enderror" value="{{ old('harga') }}" required>
                            @error('harga')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="kategori" class="form-select @error('kategori') is-invalid @enderror" required>
                                <option value="Makanan Berat" {{ old('kategori') == 'Makanan Berat' ? 'selected' : '' }}>Makanan Berat</option>
                                <option value="Makanan Ringan" {{ old('kategori') == 'Makanan Ringan' ? 'selected' : '' }}>Makanan Ringan</option>
                                <option value="Minuman" {{ old('kategori') == 'Minuman' ? 'selected' : '' }}>Minuman</option>
                            </select>
                            @error('kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="2">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gambar</label>
                            <input type="file" name="gambar" class="form-control @error('gambar') is-invalid @enderror">
                            @error('gambar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Simpan Menu</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Daftar Menu -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    
                    <!-- Search Bar -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <form action="{{ route('admin.umkm.menus.index', $umkm->id) }}" method="GET">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Cari menu berdasarkan nama..." value="{{ request('search') }}" aria-label="Cari menu">
                                    <button class="btn btn-primary px-4" type="submit">
                                        <i class="bi bi-search me-1"></i> Cari
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if($menus->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Gambar</th>
                                        <th>Info Menu</th>
                                        <th>Harga</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($menus as $menu)
                                        <tr>
                                            <td>
                                                @if($menu->gambar)
                                                    <img src="{{ asset('storage/' . $menu->gambar) }}" class="rounded" width="60" height="60" style="object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width: 60px; height: 60px;">
                                                        <i class="bi bi-image"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $menu->nama_menu }}</div>
                                                <small class="text-muted">{{ Str::limit($menu->deskripsi, 50) }}</small>
                                            </td>
                                            <td class="fw-bold text-success">
                                                Rp {{ number_format($menu->harga, 0, ',', '.') }}
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editMenu{{ $menu->id }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="{{ route('admin.umkm.menus.destroy', [$umkm->id, $menu->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus menu ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>

                                                <!-- Modal Edit -->
                                                <div class="modal fade" id="editMenu{{ $menu->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit Menu</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form action="{{ route('admin.umkm.menus.update', [$umkm->id, $menu->id]) }}" method="POST" enctype="multipart/form-data">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Nama Menu</label>
                                                                        <input type="text" name="nama_menu" class="form-control @error('nama_menu') is-invalid @enderror" value="{{ $menu->nama_menu }}" required>
                                                                        @error('nama_menu')
                                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Harga (Rp)</label>
                                                                        <input type="text" name="harga" class="form-control @error('harga') is-invalid @enderror" value="{{ $menu->harga }}" required>
                                                                        @error('harga')
                                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Kategori</label>
                                                                        <select name="kategori" class="form-select @error('kategori') is-invalid @enderror" required>
                                                                            <option value="Makanan Berat" {{ $menu->kategori == 'Makanan Berat' ? 'selected' : '' }}>Makanan Berat</option>
                                                                            <option value="Makanan Ringan" {{ $menu->kategori == 'Makanan Ringan' ? 'selected' : '' }}>Makanan Ringan</option>
                                                                            <option value="Minuman" {{ $menu->kategori == 'Minuman' ? 'selected' : '' }}>Minuman</option>
                                                                        </select>
                                                                        @error('kategori')
                                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Deskripsi</label>
                                                                        <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="2">{{ $menu->deskripsi }}</textarea>
                                                                        @error('deskripsi')
                                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Ganti Gambar (Opsional)</label>
                                                                        <input type="file" name="gambar" class="form-control @error('gambar') is-invalid @enderror">
                                                                        @error('gambar')
                                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                    <div class="d-flex justify-content-end">
                                                                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Batal</button>
                                                                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            @if(request('search'))
                                <i class="bi bi-search fs-1 text-muted"></i>
                                <h6 class="mt-2 text-muted">Menu "{{ request('search') }}" tidak ditemukan.</h6>
                                <a href="{{ route('admin.umkm.menus.index', $umkm->id) }}" class="btn btn-sm btn-outline-secondary mt-2">Reset Pencarian</a>
                            @else
                                <i class="bi bi-egg-fried fs-1 text-muted"></i>
                                <h6 class="mt-2 text-muted">Belum ada menu yang ditambahkan.</h6>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
