@extends('layouts.app')

@section('content')
{{-- ── Toast Notification Container ─────────────────────────────────────── --}}
<div id="toast-container" style="
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-width: 380px;
"></div>

<style>
.toast-notif {
    background: #fff;
    border-left: 4px solid #dc3545;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    padding: 12px 16px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    animation: slideInRight 0.3s ease;
    min-width: 280px;
}
.toast-notif.toast-success { border-left-color: #198754; }
.toast-notif.toast-warning { border-left-color: #ffc107; }
.toast-notif .toast-icon { font-size: 1.2rem; margin-top: 1px; flex-shrink: 0; }
.toast-notif .toast-body { flex: 1; }
.toast-notif .toast-title { font-weight: 600; font-size: 0.85rem; color: #333; }
.toast-notif .toast-msg   { font-size: 0.82rem; color: #555; margin-top: 2px; }
.toast-notif .toast-close {
    background: none; border: none; cursor: pointer;
    color: #aaa; font-size: 1rem; line-height: 1;
    padding: 0; margin-left: 4px;
}
.toast-notif .toast-close:hover { color: #555; }
@keyframes slideInRight {
    from { opacity: 0; transform: translateX(30px); }
    to   { opacity: 1; transform: translateX(0); }
}
@keyframes fadeOut {
    from { opacity: 1; transform: translateX(0); }
    to   { opacity: 0; transform: translateX(30px); }
}
.field-error-hint {
    font-size: 0.78rem;
    color: #dc3545;
    margin-top: 4px;
    display: none;
}
.field-error-hint.show { display: block; }
</style>

<div class="card">
    <div class="card-header fw-bold">Edit Data UMKM</div>
    <div class="card-body">

        <form action="{{ route('admin.umkms.update', $umkm->id) }}" method="POST" enctype="multipart/form-data" id="formUmkm">
            @csrf
            @method('PUT')

            {{-- ── EP-01: Nama UMKM (2–100 karakter) ──────────────────────── --}}
            <div class="mb-3">
                <label for="nama_umkm" class="form-label">Nama UMKM <span class="text-danger">*</span></label>
                <input
                    type="text"
                    name="nama_umkm"
                    id="nama_umkm"
                    class="form-control @error('nama_umkm') is-invalid @enderror"
                    value="{{ old('nama_umkm', $umkm->nama_umkm) }}"
                    maxlength="100"
                    minlength="2"
                    placeholder="Contoh: Warung Makan Berkah"
                >
                @error('nama_umkm')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="field-error-hint" id="hint-nama_umkm"></div>
            </div>

            <div class="row">
                {{-- ── EP-02: Kategori ───────────────────────────────────── --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kategori <span class="text-danger">*</span></label>
                    @php
                        if (old('kategori') !== null) {
                            $cats = old('kategori', []);
                        } else {
                            $cats = is_array($umkm->kategori)
                                ? $umkm->kategori
                                : (json_decode($umkm->kategori, true) ?? [$umkm->kategori]);
                        }
                    @endphp
                    <div class="d-flex flex-column gap-2 @error('kategori') border border-danger rounded p-2 @enderror" id="kategori-wrapper">
                        <div class="form-check">
                            <input class="form-check-input kategori-cb" type="checkbox" name="kategori[]" value="Makanan Berat" id="cat1"
                                   {{ in_array('Makanan Berat', $cats) ? 'checked' : '' }}>
                            <label class="form-check-label" for="cat1">Makanan Berat</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input kategori-cb" type="checkbox" name="kategori[]" value="Makanan Ringan" id="cat2"
                                   {{ in_array('Makanan Ringan', $cats) ? 'checked' : '' }}>
                            <label class="form-check-label" for="cat2">Makanan Ringan</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input kategori-cb" type="checkbox" name="kategori[]" value="Minuman" id="cat3"
                                   {{ in_array('Minuman', $cats) ? 'checked' : '' }}>
                            <label class="form-check-label" for="cat3">Minuman</label>
                        </div>
                    </div>
                    @error('kategori')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    <div class="field-error-hint" id="hint-kategori"></div>
                </div>

                {{-- ── EP-03: No WhatsApp (angka saja) ───────────────────── --}}
                <div class="col-md-6 mb-3">
                    <label for="no_whatsapp" class="form-label">No WhatsApp <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        name="no_whatsapp"
                        id="no_whatsapp"
                        class="form-control @error('no_whatsapp') is-invalid @enderror"
                        value="{{ old('no_whatsapp', $umkm->no_whatsapp) }}"
                        placeholder="Contoh: 6281234567890"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                    >
                    @error('no_whatsapp')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="field-error-hint" id="hint-no_whatsapp"></div>
                </div>
            </div>

            {{-- ── EP-04: Deskripsi (wajib, 2–500 karakter) ───────────────── --}}
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                <textarea
                    name="deskripsi"
                    id="deskripsi"
                    class="form-control @error('deskripsi') is-invalid @enderror"
                    rows="3"
                    maxlength="500"
                    minlength="2"
                    placeholder="Warung makan rumahan dengan menu nusantara yang lezat..."
                >{{ old('deskripsi', $umkm->deskripsi) }}</textarea>
                <div class="form-text text-muted"><span id="desc-counter">0</span>/500</div>
                @error('deskripsi')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="field-error-hint" id="hint-deskripsi"></div>
            </div>

            {{-- ── EP-05: Jadwal Operasional ───────────────────────────────── --}}
            <div class="mb-3">
                <label class="fw-bold fs-5 mb-2">Jadwal Operasional</label>

                {{-- Tampilkan error jadwal (per hari) --}}
                @php
                    $jadwalDays = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
                    $hasJadwalError = false;
                    foreach($jadwalDays as $d) {
                        if ($errors->has("jadwal_$d")) { $hasJadwalError = true; break; }
                    }
                @endphp
                @if($hasJadwalError)
                    <div class="alert alert-warning py-2 mb-2">
                        <strong>Perhatikan jadwal operasional berikut:</strong>
                        <ul class="mb-0 mt-1 small">
                            @foreach($jadwalDays as $d)
                                @error("jadwal_$d") <li>{{ $message }}</li> @enderror
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php
                    $scheduleData = [];
                    $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                    $rawJam  = $umkm->jam_operasional;
                    $rawHari = $umkm->hari_operasional;

                    if (old('jadwal') !== null) {
                        foreach($days as $d) {
                            $isBuka   = old("jadwal.$d.buka") ? true : false;
                            $startVal = old("jadwal.$d.start", '');
                            $endVal   = old("jadwal.$d.end", '');
                            $scheduleData[$d] = ['buka' => $isBuka, 'start' => $startVal, 'end' => $endVal];
                        }
                    } else {
                        foreach($days as $d) {
                            $scheduleData[$d] = ['buka' => false, 'start' => '', 'end' => ''];
                        }
                        if (str_contains($rawJam, 'Setiap Hari')) {
                            preg_match('/(\d{2}:\d{2})\s*-\s*(\d{2}:\d{2})/', $rawJam, $matches);
                            if (!empty($matches)) {
                                foreach($days as $d) {
                                    $scheduleData[$d] = ['buka' => true, 'start' => $matches[1], 'end' => $matches[2]];
                                }
                            }
                        } else {
                            foreach($days as $d) {
                                if (preg_match("/$d:\s*(\d{2}:\d{2})\s*-\s*(\d{2}:\d{2})/i", $rawJam, $matches)) {
                                    $scheduleData[$d] = ['buka' => true, 'start' => $matches[1], 'end' => $matches[2]];
                                } elseif (str_contains($rawHari, $d) && preg_match('/(\d{2}:\d{2})\s*-\s*(\d{2}:\d{2})/', $rawJam, $matches)) {
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
                            @php
                                $data   = $scheduleData[$day];
                                $hasErr = $errors->has("jadwal_$day");
                            @endphp
                            <tr class="{{ $hasErr ? 'table-warning' : '' }}" id="row_{{ $day }}">
                                <td class="align-middle fw-bold">{{ $day }}</td>
                                <td class="align-middle">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input day-toggle"
                                               type="checkbox"
                                               name="jadwal[{{ $day }}][buka]"
                                               value="1"
                                               id="check_{{ $day }}"
                                               onchange="toggleTime('{{ $day }}')"
                                               {{ $data['buka'] ? 'checked' : '' }}>
                                        <label class="form-check-label" for="check_{{ $day }}">Buka</label>
                                    </div>
                                </td>
                                <td>
                                    <input type="time"
                                           name="jadwal[{{ $day }}][start]"
                                           id="start_{{ $day }}"
                                           class="form-control form-control-sm time-start @if($hasErr) is-invalid @endif"
                                           value="{{ $data['start'] }}"
                                           {{ !$data['buka'] ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <input type="time"
                                           name="jadwal[{{ $day }}][end]"
                                           id="end_{{ $day }}"
                                           class="form-control form-control-sm time-end @if($hasErr) is-invalid @endif"
                                           value="{{ $data['end'] }}"
                                           {{ !$data['buka'] ? 'disabled' : '' }}>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="field-error-hint" id="hint-jadwal"></div>
            </div>

            {{-- ── EP-06: Alamat Lengkap (2–255 karakter) ─────────────────── --}}
            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                <textarea
                    name="alamat"
                    id="alamat"
                    class="form-control @error('alamat') is-invalid @enderror"
                    rows="2"
                    maxlength="255"
                    minlength="2"
                    placeholder="Contoh: Jl. Merdeka No. 10, Bandung, Jawa Barat"
                >{{ old('alamat', $umkm->alamat) }}</textarea>
                @error('alamat')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="field-error-hint" id="hint-alamat"></div>
            </div>

            {{-- ── EP-07: Link Google Maps (opsional, harus diawali https://) --}}
            <div class="mb-3">
                <label for="koordinat" class="form-label">Link Google Maps <span class="text-muted">(Opsional)</span></label>
                <input
                    type="url"
                    name="koordinat"
                    id="koordinat"
                    class="form-control @error('koordinat') is-invalid @enderror"
                    value="{{ old('koordinat', $umkm->koordinat) }}"
                    placeholder="Contoh: https://maps.google.com/xyz"
                >
                @error('koordinat')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="field-error-hint" id="hint-koordinat"></div>
            </div>

            {{-- ── EP-08: Upload Gambar (jpg/jpeg/png ≤ 2MB, opsional) ────── --}}
            <div class="mb-3">
                <label for="gambar" class="form-label">Gambar <span class="text-muted">(Opsional — biarkan kosong jika tidak ingin mengubah)</span></label>
                <input
                    type="file"
                    name="gambar"
                    id="gambar"
                    class="form-control mb-2 @error('gambar') is-invalid @enderror"
                    accept=".jpg,.jpeg,.png"
                >
                @error('gambar')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="field-error-hint" id="hint-gambar"></div>
                @if($umkm->gambar)
                    <div class="alert alert-info py-2 mt-2">
                        <small>Gambar saat ini: <a href="{{ asset('storage/' . $umkm->gambar) }}" target="_blank">Lihat Gambar</a></small>
                    </div>
                @endif
            </div>

            {{-- ── EP-09: Bisa Delivery ─────────────────────────────────────── --}}
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="is_delivery" value="1"
                       id="del" {{ old('is_delivery', $umkm->is_delivery) ? 'checked' : '' }}>
                <label class="form-check-label" for="del">Bisa Delivery?</label>
            </div>

            {{-- ── Action Buttons ──────────────────────────────────────────── --}}
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success" id="btnSubmit">Update Data</button>
                <a href="{{ route('admin.umkms.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- ── CLIENT-SIDE VALIDATION SCRIPT ─────────────────────────────────── --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<script>
/* ── Toast helper ─────────────────────────────────────────────────────── */
function showToast(message, type = 'error', title = null) {
    const container = document.getElementById('toast-container');
    const icons  = { error: '❌', success: '✅', warning: '⚠️' };
    const titles = { error: 'Error Validasi', success: 'Berhasil', warning: 'Perhatian' };
    const cssClass = type === 'error' ? '' : (type === 'success' ? 'toast-success' : 'toast-warning');

    const toast = document.createElement('div');
    toast.className = `toast-notif ${cssClass}`;
    toast.innerHTML = `
        <span class="toast-icon">${icons[type]}</span>
        <div class="toast-body">
            <div class="toast-title">${title || titles[type]}</div>
            <div class="toast-msg">${message}</div>
        </div>
        <button class="toast-close" onclick="this.closest('.toast-notif').remove()">×</button>
    `;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.3s ease forwards';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

/* ── Inline hint helpers ──────────────────────────────────────────────── */
function setFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const hint  = document.getElementById('hint-' + fieldId);
    if (field) { field.classList.add('is-invalid'); field.classList.remove('is-valid'); }
    if (hint)  { hint.textContent = message; hint.classList.add('show'); }
}

function clearFieldError(fieldId) {
    const field = document.getElementById(fieldId);
    const hint  = document.getElementById('hint-' + fieldId);
    if (field) { field.classList.remove('is-invalid'); field.classList.add('is-valid'); }
    if (hint)  { hint.textContent = ''; hint.classList.remove('show'); }
}

/* ── Validation rules (EP table) ─────────────────────────────────────── */
const RULES = {
    nama_umkm: {
        validate(val) {
            if (!val || val.trim() === '')       return 'Nama UMKM tidak boleh kosong.';
            if (val.trim().length < 2)           return 'Nama UMKM minimal 2 karakter.';
            if (val.trim().length > 100)         return 'Nama UMKM tidak boleh melebihi 100 karakter.';
            if (!/^[\p{L}0-9\s\-'\.\&,()\/ ]+$/u.test(val.trim()))
                                                 return 'Nama UMKM hanya boleh mengandung huruf, angka, dan spasi.';
            return null;
        }
    },
    no_whatsapp: {
        validate(val) {
            if (!val || val.trim() === '') return 'No WhatsApp tidak boleh kosong.';
            if (!/^[0-9]+$/.test(val.trim())) return 'No WhatsApp hanya boleh berisi angka (contoh: 6281234567890).';
            return null;
        }
    },
    deskripsi: {
        validate(val) {
            if (!val || val.trim() === '') return 'Deskripsi tidak boleh kosong.';
            if (val.trim().length < 2)     return 'Deskripsi minimal 2 karakter.';
            if (val.trim().length > 500)   return 'Deskripsi tidak boleh melebihi 500 karakter.';
            return null;
        }
    },
    alamat: {
        validate(val) {
            if (!val || val.trim() === '') return 'Alamat Lengkap tidak boleh kosong.';
            if (val.trim().length < 2)     return 'Alamat Lengkap minimal 2 karakter.';
            if (val.trim().length > 255)   return 'Alamat Lengkap tidak boleh melebihi 255 karakter.';
            return null;
        }
    },
    koordinat: {
        validate(val) {
            if (!val || val.trim() === '') return null; // opsional
            if (!val.startsWith('https://')) return 'Link Google Maps harus diawali dengan https://.';
            try { new URL(val); } catch(e) { return 'Format Link Google Maps tidak valid — harap masukkan URL yang benar.'; }
            return null;
        }
    }
};

/* ── Validate Kategori ────────────────────────────────────────────────── */
function validateKategori(showHint = true) {
    const checked = document.querySelectorAll('.kategori-cb:checked').length;
    const wrapper = document.getElementById('kategori-wrapper');
    const hint    = document.getElementById('hint-kategori');
    if (checked === 0) {
        if (wrapper) { wrapper.classList.add('border', 'border-danger', 'rounded', 'p-2'); }
        if (showHint && hint) { hint.textContent = 'Harap pilih minimal satu kategori.'; hint.classList.add('show'); }
        return 'Harap pilih minimal satu kategori.';
    } else {
        if (wrapper) { wrapper.classList.remove('border', 'border-danger', 'rounded', 'p-2'); }
        if (hint)    { hint.textContent = ''; hint.classList.remove('show'); }
        return null;
    }
}

/* ── Validate Gambar ──────────────────────────────────────────────────── */
function validateGambar(showToastMsg = false) {
    const input = document.getElementById('gambar');
    const hint  = document.getElementById('hint-gambar');
    if (!input || !input.files || input.files.length === 0) return null; // opsional

    const file = input.files[0];
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    const maxSize = 2 * 1024 * 1024;

    if (!allowedTypes.includes(file.type)) {
        const msg = 'Format file tidak didukung. Harap upload file JPG, JPEG, atau PNG.';
        input.classList.add('is-invalid');
        if (hint) { hint.textContent = msg; hint.classList.add('show'); }
        if (showToastMsg) showToast(msg);
        return msg;
    }
    if (file.size > maxSize) {
        const msg = 'Ukuran file terlalu besar. Maksimal ukuran file adalah 2MB.';
        input.classList.add('is-invalid');
        if (hint) { hint.textContent = msg; hint.classList.add('show'); }
        if (showToastMsg) showToast(msg);
        return msg;
    }

    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
    if (hint) { hint.textContent = ''; hint.classList.remove('show'); }
    return null;
}

/* ── Validate Jadwal ──────────────────────────────────────────────────── */
function validateJadwal() {
    const days = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
    const errors = [];

    days.forEach(day => {
        const toggle = document.getElementById('check_' + day);
        if (!toggle || !toggle.checked) return;

        const startEl = document.getElementById('start_' + day);
        const endEl   = document.getElementById('end_' + day);
        const start   = startEl ? startEl.value.trim() : '';
        const end     = endEl   ? endEl.value.trim()   : '';

        if (startEl) startEl.classList.remove('is-invalid');
        if (endEl)   endEl.classList.remove('is-invalid');

        if (!start) {
            if (startEl) startEl.classList.add('is-invalid');
            errors.push(`Jam buka wajib diisi untuk hari ${day} yang berstatus Buka.`);
        } else if (!end) {
            if (endEl) endEl.classList.add('is-invalid');
            errors.push(`Jam tutup wajib diisi untuk hari ${day} yang berstatus Buka.`);
        } else if (end <= start) {
            if (startEl) startEl.classList.add('is-invalid');
            if (endEl)   endEl.classList.add('is-invalid');
            errors.push(`Jam tutup tidak boleh sama atau lebih awal dari jam buka untuk hari ${day}.`);
        }
    });

    const hint = document.getElementById('hint-jadwal');
    if (errors.length > 0) {
        if (hint) { hint.textContent = errors[0]; hint.classList.add('show'); }
    } else {
        if (hint) { hint.textContent = ''; hint.classList.remove('show'); }
    }
    return errors;
}

/* ── Attach event listeners on DOMContentLoaded ───────────────────────── */
document.addEventListener('DOMContentLoaded', function () {

    // Character counter deskripsi
    const descEl = document.getElementById('deskripsi');
    const descCounter = document.getElementById('desc-counter');
    if (descEl) {
        if (descCounter) descCounter.textContent = descEl.value.length;
        descEl.addEventListener('input', function () {
            if (descCounter) descCounter.textContent = this.value.length;
        });
    }

    // Blur + input validation for text fields
    Object.keys(RULES).forEach(fieldId => {
        const el = document.getElementById(fieldId);
        if (!el) return;

        el.addEventListener('blur', function () {
            const err = RULES[fieldId].validate(this.value);
            if (err) {
                setFieldError(fieldId, err);
                showToast(err);
            } else {
                clearFieldError(fieldId);
            }
        });

        el.addEventListener('input', function () {
            const err = RULES[fieldId].validate(this.value);
            if (err) {
                setFieldError(fieldId, err);
            } else {
                clearFieldError(fieldId);
            }
        });
    });

    // Kategori checkbox change
    document.querySelectorAll('.kategori-cb').forEach(cb => {
        cb.addEventListener('change', () => validateKategori(true));
    });

    // Gambar file change
    const gambarInput = document.getElementById('gambar');
    if (gambarInput) {
        gambarInput.addEventListener('change', () => validateGambar(true));
    }

    // Jadwal time inputs change
    ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'].forEach(day => {
        const startEl = document.getElementById('start_' + day);
        const endEl   = document.getElementById('end_' + day);
        if (startEl) startEl.addEventListener('change', validateJadwal);
        if (endEl)   endEl.addEventListener('change', validateJadwal);
    });

    // Inisialisasi opacity jadwal
    ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'].forEach(day => {
        const toggle  = document.getElementById('check_' + day);
        const startEl = document.getElementById('start_' + day);
        const endEl   = document.getElementById('end_'   + day);
        if (toggle && !toggle.checked) {
            startEl.style.opacity = '0.4';
            endEl.style.opacity   = '0.4';
        }
    });

    // ── Form submit validation ───────────────────────────────────────────
    document.getElementById('formUmkm').addEventListener('submit', function (e) {
        const fieldErrors = [];

        Object.keys(RULES).forEach(fieldId => {
            const el = document.getElementById(fieldId);
            if (!el) return;
            const err = RULES[fieldId].validate(el.value);
            if (err) {
                setFieldError(fieldId, err);
                fieldErrors.push(err);
            } else {
                clearFieldError(fieldId);
            }
        });

        const katErr = validateKategori(true);
        if (katErr) fieldErrors.push(katErr);

        const gamErr = validateGambar(false);
        if (gamErr) fieldErrors.push(gamErr);

        const jadwalErrs = validateJadwal();
        jadwalErrs.forEach(err => fieldErrors.push(err));

        if (fieldErrors.length > 0) {
            e.preventDefault();
            showToast(
                `Ditemukan ${fieldErrors.length} kesalahan pada form. Harap periksa kembali setiap field.`,
                'error',
                'Form Tidak Valid'
            );
            const firstInvalid = document.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalid.focus();
            }
        }
    });
});

/* ── Jadwal toggle functions ─────────────────────────────────────────── */
function toggleTime(day) {
    const toggle  = document.getElementById('check_' + day);
    const startEl = document.getElementById('start_' + day);
    const endEl   = document.getElementById('end_' + day);
    const isOpen  = toggle.checked;

    startEl.disabled = !isOpen;
    endEl.disabled   = !isOpen;

    if (isOpen) {
        if (!startEl.value) startEl.value = '08:00';
        if (!endEl.value)   endEl.value   = '17:00';
        startEl.style.opacity = '1';
        endEl.style.opacity   = '1';
    } else {
        startEl.value = '';
        endEl.value   = '';
        startEl.style.opacity = '0.4';
        endEl.style.opacity   = '0.4';
        startEl.classList.remove('is-invalid');
        endEl.classList.remove('is-invalid');
    }
    validateJadwal();
}

function toggleAll(state) {
    document.querySelectorAll('.day-toggle').forEach(el => {
        el.checked = state;
        el.dispatchEvent(new Event('change'));
    });
}

function copyMonday() {
    const senin = document.getElementById('check_Senin');
    if (!senin.checked) {
        showToast('Aktifkan dan isi jadwal hari Senin terlebih dahulu!', 'warning');
        return;
    }
    const start = document.getElementById('start_Senin').value;
    const end   = document.getElementById('end_Senin').value;
    if (!start || !end) {
        showToast('Harap isi Jam Buka dan Jam Tutup Senin terlebih dahulu!', 'warning');
        return;
    }
    ['Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'].forEach(day => {
        const toggle = document.getElementById('check_' + day);
        toggle.checked = true;
        toggle.dispatchEvent(new Event('change'));
        document.getElementById('start_' + day).value = start;
        document.getElementById('end_'   + day).value = end;
    });
}
</script>
@endsection