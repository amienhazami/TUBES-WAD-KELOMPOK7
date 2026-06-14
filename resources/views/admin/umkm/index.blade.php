@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-0 fw-bold text-dark">Kelola Data UMKM</h4>
                        <p class="text-muted small mb-0">Atur dan kelola daftar UMKM yang terdaftar.</p>
                    </div>
                    <a href="{{ route('admin.umkms.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                        <i class="bi bi-plus-lg"></i> Tambah UMKM
                    </a>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <form action="{{ route('admin.umkms.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Cari UMKM berdasarkan nama..." value="{{ request('search') }}" aria-label="Cari UMKM">
                                <button class="btn btn-primary px-4" type="submit">
                                    <i class="bi bi-search me-1"></i> Cari
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="border-0 rounded-start ps-4">No</th>
                                <th class="border-0">Informasi UMKM</th>
                                <th class="border-0">Kategori</th>
                                <th class="border-0">Kontak</th>
                                <th class="border-0 rounded-end text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($umkms as $index => $u)
                            <tr>
                                <td class="ps-4 fw-bold text-secondary">{{ $index + 1 + ($umkms->currentPage() - 1) * $umkms->perPage() }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($u->gambar)
                                            <img src="{{ asset('storage/'.$u->gambar) }}" class="rounded me-3" width="50" height="50" style="object-fit: cover;">
                                        @else
                                            <div class="rounded me-3 bg-light d-flex align-items-center justify-content-center text-muted" style="width: 50px; height: 50px;">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">{{ $u->nama_umkm }}</h6>
                                            <small class="text-muted d-block mb-1">{{ Str::limit($u->deskripsi, 40) }}</small>
                                            @if($u->status_buka == 'Buka')
                                                <span class="badge bg-success" style="font-size: 0.65rem;">BUKA</span>
                                            @else
                                                <span class="badge bg-danger" style="font-size: 0.65rem;">TUTUP</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $cats = is_array($u->kategori) ? $u->kategori : [$u->kategori];
                                    @endphp
                                    @foreach($cats as $cat)
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-1 fw-normal mb-1">
                                            {{ $cat }}
                                        </span>
                                    @endforeach
                                </td>
                                <td>
                                    <a href="https://wa.me/{{ $u->no_whatsapp }}" target="_blank" class="text-decoration-none text-success">
                                        <i class="bi bi-whatsapp me-1"></i> {{ $u->no_whatsapp }}
                                    </a>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.umkm.menus.index', $u->id) }}" class="btn btn-sm btn-outline-primary" title="Kelola Menu">
                                            <i class="bi bi-list-task"></i> Menu
                                        </a>
                                        <a href="{{ route('admin.umkms.edit', $u->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <form action="{{ route('admin.umkms.destroy', $u->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <div class="mb-2"><i class="bi bi-search fs-1"></i></div>
                                    <h6 class="fw-bold">Data tidak ditemukan</h6>
                                    <p class="small mb-0">Belum ada UMKM yang ditambahkan atau pencarian tidak cocok.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 d-flex justify-content-end">
                    @if(method_exists($umkms, 'links'))
                        {{ $umkms->links() }}
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
@endsection