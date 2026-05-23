@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header fw-bold">Edit Data UMKM</div>
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

        <form action="{{ route('admin.umkms.update', $umkm->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label>Nama UMKM</label>
                <input type="text" name="nama_umkm" class="form-control" value="{{ old('nama_umkm', $umkm->nama_umkm) }}" required>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Kategori</label>
                    <div class="d-flex flex-column gap-2">
                        @php
                            if (old('kategori') !== null) {
                                $cats = old('kategori', []);
                            } else {
                                $cats = is_array($umkm->kategori) ? $umkm->kategori : (json_decode($umkm->kategori, true) ?? [$umkm->kategori]);
                            }
                        @endphp
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="kategori[]" value="Makanan Berat" id="cat1" {{ in_array('Makanan Berat', $cats) ? 'checked' : '' }}>
                            <label class="form-check-label" for="cat1">Makanan Berat</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="kategori[]" value="Makanan Ringan" id="cat2" {{ in_array('Makanan Ringan', $cats) ? 'checked' : '' }}>
                            <label class="form-check-label" for="cat2">Makanan Ringan</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="kategori[]" value="Minuman" id="cat3" {{ in_array('Minuman', $cats) ? 'checked' : '' }}>
                            <label class="form-check-label" for="cat3">Minuman</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label>No WhatsApp</label>
                    <input type="text" name="no_whatsapp" class="form-control" value="{{ old('no_whatsapp', $umkm->no_whatsapp) }}" required>
                </div>
            </div>

            <div class="mb-3">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control" rows="3" required>{{ old('deskripsi', $umkm->deskripsi) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="fw-bold fs-5 mb-2">Jadwal Operasional</label>
                
                @php
                    $scheduleData = [];
                    $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                    $rawJam = $umkm->jam_operasional; 
                    $rawHari = $umkm->hari_operasional;

                    // If there is old input from a failed validation, use that. Otherwise use database values.
                    if (old('jadwal') !== null) {
                        foreach($days as $d) {
                            $isBuka = old("jadwal.$d.buka") ? true : false;
                            $startVal = old("jadwal.$d.start", '');
                            $endVal = old("jadwal.$d.end", '');
                            $scheduleData[$d] = ['buka' => $isBuka, 'start' => $startVal, 'end' => $endVal];
                        }
                    } else {
                        // Initialize default (Closed)
                        foreach($days as $d) {
                            $scheduleData[$d] = ['buka' => false, 'start' => '', 'end' => ''];
                        }

                        // Check for "Setiap Hari" format
                        if (str_contains($rawJam, 'Setiap Hari')) {
                            // Extract time
                            preg_match('/(\d{2}:\d{2})\s*-\s*(\d{2}:\d{2})/', $rawJam, $matches);
                            if (!empty($matches)) {
                                foreach($days as $d) {
                                    $scheduleData[$d] = ['buka' => true, 'start' => $matches[1], 'end' => $matches[2]];
                                }
                            }
                        } else {
                            // Try parsing specific lines: "Senin: 08:00 - 17:00"
                            foreach($days as $d) {
                                if (preg_match("/$d:\s*(\d{2}:\d{2})\s*-\s*(\d{2}:\d{2})/i", $rawJam, $matches)) {
                                    $scheduleData[$d] = ['buka' => true, 'start' => $matches[1], 'end' => $matches[2]];
                                } elseif (str_contains($rawHari, $d) && preg_match('/(\d{2}:\d{2})\s*-\s*(\d{2}:\d{2})/', $rawJam, $matches)) {
                                    // Fallback: Day is in "hari_operasional" string and we have a generic time in "jam_operasional"
                                    $scheduleData[$d] = ['buka' => true, 'start' => $matches[1], 'end' => $matches[2]];
                                }
                            }
                        }
                    }
                @endphp

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
                            @foreach($days as $day)
                            @php $data = $scheduleData[$day]; @endphp
                            <tr>
                                <td class="align-middle fw-bold">{{ $day }}</td>
                                <td class="align-middle">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input day-toggle" type="checkbox" name="jadwal[{{ $day }}][buka]" value="1" id="check_{{ $day }}" onchange="toggleTime('{{ $day }}')" {{ $data['buka'] ? 'checked' : '' }}>
                                        <label class="form-check-label" for="check_{{ $day }}">Buka</label>
                                    </div>
                                </td>
                                <td>
                                    <input type="time" name="jadwal[{{ $day }}][start]" id="start_{{ $day }}" class="form-control form-control-sm time-start" value="{{ $data['start'] }}" {{ !$data['buka'] ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <input type="time" name="jadwal[{{ $day }}][end]" id="end_{{ $day }}" class="form-control form-control-sm time-end" value="{{ $data['end'] }}" {{ !$data['buka'] ? 'disabled' : '' }}>
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
                    const startInput = document.getElementById('start_' + day);
                    const endInput = document.getElementById('end_' + day);
                    
                    startInput.disabled = !isChecked;
                    endInput.disabled = !isChecked;
                    
                    if(isChecked) {
                        if(!startInput.value) startInput.value = '08:00';
                        if(!endInput.value) endInput.value = '17:00';
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
                <textarea name="alamat" class="form-control" rows="2" required>{{ old('alamat', $umkm->alamat) }}</textarea>
            </div>

            <div class="mb-3">
                <label>Link Google Maps / Koordinat (Opsional)</label>
                <input type="text" name="koordinat" class="form-control" value="{{ old('koordinat', $umkm->koordinat) }}" placeholder="Contoh: https://maps.google.com/...">
            </div>
            
            <div class="mb-3">
                <label>Gambar (Biarkan kosong jika tidak ingin mengubah)</label>
                <input type="file" name="gambar" class="form-control mb-2">
                @if($umkm->gambar)
                    <div class="alert alert-info py-2">
                        <small>Gambar saat ini: <a href="{{ asset('storage/' . $umkm->gambar) }}" target="_blank">Lihat Gambar</a></small>
                    </div>
                @endif
            </div>
            
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="is_delivery" value="1" id="del" {{ old('is_delivery', $umkm->is_delivery) ? 'checked' : '' }}>
                <label class="form-check-label" for="del">Menyediakan Layanan Delivery?</label>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">Update Data</button>
                <a href="{{ route('admin.umkms.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection