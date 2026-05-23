@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">Tambah UMKM Baru</div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.umkms.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-3">
                <label>Nama UMKM</label>
                <input type="text" name="nama_umkm" class="form-control" value="{{ old('nama_umkm') }}" required>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Kategori</label>
                    <div class="d-flex flex-column gap-2">
                        @php $oldKategori = old('kategori', []); @endphp
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="kategori[]" value="Makanan Berat" id="cat1" {{ in_array('Makanan Berat', $oldKategori) ? 'checked' : '' }}>
                            <label class="form-check-label" for="cat1">Makanan Berat</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="kategori[]" value="Makanan Ringan" id="cat2" {{ in_array('Makanan Ringan', $oldKategori) ? 'checked' : '' }}>
                            <label class="form-check-label" for="cat2">Makanan Ringan</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="kategori[]" value="Minuman" id="cat3" {{ in_array('Minuman', $oldKategori) ? 'checked' : '' }}>
                            <label class="form-check-label" for="cat3">Minuman</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label>No WhatsApp</label>
                    <input type="text" name="no_whatsapp" class="form-control" value="{{ old('no_whatsapp') }}" required>
                </div>
            </div>

            <div class="mb-3">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control" rows="3" required>{{ old('deskripsi') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="fw-bold fs-5 mb-2">Jadwal Operasional</label>
                
                <div class="d-flex gap-2 mb-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAll(true)">Buka Semua</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAll(false)">Tutup Semua</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyMonday()">Samakan dengan Senin</button>
                </div>

                <div class="table-responsive bg-light p-3 rounded">
                    <table class="table table-borderless table-sm mb-0">
                        <thead>
                            <tr>
                                <th style="width: 150px;">Hari</th>
                                <th>Status</th>
                                <th>Jam Buka</th>
                                <th>Jam Tutup</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $day)
                            @php
                                $isBuka = old("jadwal.$day.buka") ? true : false;
                                $startVal = old("jadwal.$day.start", '');
                                $endVal = old("jadwal.$day.end", '');
                            @endphp
                            <tr>
                                <td class="align-middle fw-bold">{{ $day }}</td>
                                <td class="align-middle">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input day-toggle" type="checkbox" name="jadwal[{{ $day }}][buka]" value="1" id="check_{{ $day }}" onchange="toggleTime('{{ $day }}')" {{ $isBuka ? 'checked' : '' }}>
                                        <label class="form-check-label" for="check_{{ $day }}">Buka</label>
                                    </div>
                                </td>
                                <td>
                                    <input type="time" name="jadwal[{{ $day }}][start]" id="start_{{ $day }}" class="form-control form-control-sm" value="{{ $startVal }}" {{ !$isBuka ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <input type="time" name="jadwal[{{ $day }}][end]" id="end_{{ $day }}" class="form-control form-control-sm" value="{{ $endVal }}" {{ !$isBuka ? 'disabled' : '' }}>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <script>
                function toggleTime(day) {
                    const isChecked = document.getElementById('check_' + day).checked;
                    document.getElementById('start_' + day).disabled = !isChecked;
                    document.getElementById('end_' + day).disabled = !isChecked;
                    
                    if(isChecked) {
                        if(!document.getElementById('start_' + day).value) document.getElementById('start_' + day).value = '08:00';
                        if(!document.getElementById('end_' + day).value) document.getElementById('end_' + day).value = '17:00';
                    }
                }

                function toggleAll(state) {
                    document.querySelectorAll('.day-toggle').forEach(el => {
                        el.checked = state;
                        // Trigger change event to update disabled state
                        el.dispatchEvent(new Event('change'));
                    });
                }

                function copyMonday() {
                    if(!document.getElementById('check_Senin').checked) {
                        alert('Aktifkan hari Senin terlebih dahulu!');
                        return;
                    }
                    const start = document.getElementById('start_Senin').value;
                    const end = document.getElementById('end_Senin').value;

                    ['Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'].forEach(day => {
                        document.getElementById('check_' + day).checked = true;
                        document.getElementById('check_' + day).dispatchEvent(new Event('change'));
                        document.getElementById('start_' + day).value = start;
                        document.getElementById('end_' + day).value = end;
                    });
                }
            </script>

            <div class="mb-3">
                <label>Alamat Lengkap</label>
                <textarea name="alamat" class="form-control" rows="2" required>{{ old('alamat') }}</textarea>
            </div>

            <div class="mb-3">
                <label>Link Google Maps / Koordinat (Opsional)</label>
                <input type="text" name="koordinat" class="form-control" value="{{ old('koordinat') }}" placeholder="Contoh: https://maps.google.com/...">
            </div>

            <div class="mb-3">
                <label>Gambar</label>
                <input type="file" name="gambar" class="form-control">
            </div>
            
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_delivery" value="1" id="del" {{ old('is_delivery') ? 'checked' : '' }}>
                <label class="form-check-label" for="del">Bisa Delivery?</label>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.umkms.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection