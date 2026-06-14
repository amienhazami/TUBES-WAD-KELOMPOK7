<?php

namespace App\Http\Controllers;
use App\Models\Umkm;
use App\Http\Requests\UmkmStoreRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminUmkmController extends Controller
{
    //menampilkan daftar umkm
    public function index(Request $request) {
        $query = Umkm::latest();
        if ($request->search) {
            $query->where('nama_umkm', 'like', '%'.$request->search.'%');
        }
        $umkms = $query->paginate(10);
        return view('admin.umkm.index', compact('umkms'));
    }

    //form tambah umkm
    public function create() { return view('admin.umkm.create'); }

    //simpan umkm baru
    public function store(UmkmStoreRequest $request) {
        // Validasi field utama sudah ditangani oleh UmkmStoreRequest (DTT-01 s/d DTT-09)
        $validated = $request->validated();

        // ── DTT-05: Validasi Jadwal Operasional ──────────────────────────────
        // Cek per hari: jika status buka aktif, jam buka & tutup wajib diisi
        // dan jam tutup tidak boleh sama atau lebih kecil dari jam buka
        $jadwal     = $request->input('jadwal', []);
        $daysOrder  = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $jadwalErrors = [];

        foreach ($daysOrder as $day) {
            $times = $jadwal[$day] ?? [];

            // Hanya validasi jika hari di-set Buka (toggle aktif)
            if (!isset($times['buka'])) {
                continue; // DTT-05 R4: hari Tutup, lewati
            }

            $start = trim($times['start'] ?? '');
            $end   = trim($times['end']   ?? '');

            // DTT-05 R2: status Buka tapi jam buka kosong
            if (empty($start)) {
                $jadwalErrors["jadwal_{$day}_start"] = "Jam buka wajib diisi untuk hari {$day} yang berstatus Buka.";
                continue;
            }

            // DTT-05 R3: status Buka tapi jam tutup kosong
            if (empty($end)) {
                $jadwalErrors["jadwal_{$day}_end"] = "Jam tutup wajib diisi untuk hari {$day} yang berstatus Buka.";
                continue;
            }

            // DTT-05 R5: jam tutup tidak boleh sama atau lebih awal dari jam buka
            if (strtotime($end) <= strtotime($start)) {
                $jadwalErrors["jadwal_{$day}_range"] = "Jam tutup tidak boleh lebih awal atau sama dengan jam buka untuk hari {$day}.";
            }
        }

        if (!empty($jadwalErrors)) {
            return back()->withErrors($jadwalErrors)->withInput();
        }

        // ── Susun data hari & jam operasional ────────────────────────────────
        $bukaDays   = [];
        $jamStrings = [];

        foreach ($daysOrder as $day) {
            if (isset($jadwal[$day]['buka'])) {
                $bukaDays[]   = $day;
                $start        = $jadwal[$day]['start'];
                $end          = $jadwal[$day]['end'];
                $jamStrings[] = "{$day}: {$start} - {$end}";
            }
        }

        $validated['hari_operasional'] = !empty($bukaDays)
            ? implode(', ', $bukaDays)
            : 'Tutup Sementara';

        // Format jam operasional: ringkas jika semua hari sama, detail jika berbeda
        $uniqueTimes = array_unique(array_map(function ($day) use ($jadwal) {
            return ($jadwal[$day]['start'] ?? '') . ' - ' . ($jadwal[$day]['end'] ?? '');
        }, $bukaDays));

        if (empty($bukaDays)) {
            $validated['jam_operasional'] = '-';
        } elseif (count($uniqueTimes) === 1 && count($bukaDays) === 7) {
            $validated['jam_operasional'] = 'Setiap Hari: ' . reset($uniqueTimes);
        } elseif (count($uniqueTimes) === 1) {
            $validated['jam_operasional'] = implode(', ', $bukaDays) . ': ' . reset($uniqueTimes);
        } else {
            $validated['jam_operasional'] = implode("\n", $jamStrings);
        }

        // ── Handle field khusus ───────────────────────────────────────────────
        // DTT-09: is_delivery — boolean dari checkbox
        $validated['is_delivery'] = $request->has('is_delivery');

        // DTT-08: Upload gambar — simpan ke storage jika ada file
        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('umkm', 'public');
        } else {
            unset($validated['gambar']); // kolom nullable, tidak perlu di-set
        }

        // ── Simpan ke database ────────────────────────────────────────────────
        Umkm::create($validated);

        // DTT-09 R1: redirect sukses dengan flash message
        return redirect()
            ->route('admin.umkms.index')
            ->with('success', 'UMKM berhasil ditambahkan ke dalam sistem.');
    }

    //form edit umkm
    public function edit($id) {
        $umkm = Umkm::findOrFail($id);
        return view('admin.umkm.edit', compact('umkm'));
    }

    //update data umkm
    public function update(Request $request, $id) {
        $umkm = Umkm::findOrFail($id);

        // ── Validasi sesuai Equivalence Partitioning (EP) ────────────────────
        $request->validate([
            // EP-01: Nama UMKM — 2–100 karakter, huruf, angka & spasi
            'nama_umkm'   => ['required', 'string', 'min:2', 'max:100',
                              'regex:/^[\p{L}0-9\s\-\'\.&,()\/ ]+$/u'],

            // EP-02: Kategori — minimal 1 dipilih
            'kategori'    => ['required', 'array', 'min:1'],
            'kategori.*'  => ['in:Makanan Berat,Makanan Ringan,Minuman'],

            // EP-03: No WhatsApp — angka saja
            'no_whatsapp' => ['required', 'regex:/^[0-9]+$/'],

            // EP-04: Deskripsi — wajib, 2–500 karakter
            'deskripsi'   => ['required', 'string', 'min:2', 'max:500'],

            // EP-06: Alamat Lengkap — wajib, 2–255 karakter
            'alamat'      => ['required', 'string', 'min:2', 'max:255'],

            // EP-07: Link Google Maps — opsional, wajib diawali https://
            'koordinat'   => ['nullable', 'url', 'regex:/^https:\/\//'],

            // EP-08: Gambar — opsional, jpg/jpeg/png, maks 2MB
            'gambar'      => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'nama_umkm.required'  => 'Nama UMKM tidak boleh kosong.',
            'nama_umkm.min'       => 'Nama UMKM minimal harus terdiri dari 2 karakter.',
            'nama_umkm.max'       => 'Nama UMKM tidak boleh melebihi 100 karakter.',
            'nama_umkm.regex'     => 'Nama UMKM hanya boleh mengandung huruf, angka, dan spasi.',
            'kategori.required'   => 'Harap pilih minimal satu kategori.',
            'kategori.min'        => 'Harap pilih minimal satu kategori.',
            'kategori.*.in'       => 'Kategori yang dipilih tidak valid.',
            'no_whatsapp.required'=> 'No WhatsApp tidak boleh kosong.',
            'no_whatsapp.regex'   => 'No WhatsApp hanya boleh berisi angka (contoh: 6281234567890).',
            'deskripsi.required'  => 'Deskripsi tidak boleh kosong.',
            'deskripsi.min'       => 'Deskripsi minimal harus terdiri dari 2 karakter.',
            'deskripsi.max'       => 'Deskripsi tidak boleh melebihi 500 karakter.',
            'alamat.required'     => 'Alamat Lengkap tidak boleh kosong.',
            'alamat.min'          => 'Alamat Lengkap minimal harus terdiri dari 2 karakter.',
            'alamat.max'          => 'Alamat Lengkap tidak boleh melebihi 255 karakter.',
            'koordinat.url'       => 'Format Link Google Maps tidak valid — harap masukkan URL yang benar.',
            'koordinat.regex'     => 'Link Google Maps harus diawali dengan https://.',
            'gambar.image'        => 'File yang diunggah harus berupa gambar.',
            'gambar.mimes'        => 'Format file tidak didukung. Harap upload JPG, JPEG, atau PNG.',
            'gambar.max'          => 'Ukuran file terlalu besar. Maksimal ukuran file adalah 2MB.',
        ]);

        // ── EP-05: Validasi Jadwal Operasional ──────────────────────────────
        // Jika hari Buka: jam buka wajib < jam tutup
        $jadwal = $request->input('jadwal', []);
        $jadwalErrors = [];
        $daysOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        foreach ($daysOrder as $day) {
            $times = $jadwal[$day] ?? [];
            if (!isset($times['buka'])) continue; // hari Tutup, lewati

            $start = trim($times['start'] ?? '');
            $end   = trim($times['end']   ?? '');

            if (empty($start)) {
                $jadwalErrors["jadwal_{$day}"] = "Jam buka wajib diisi untuk hari {$day} yang berstatus Buka.";
                continue;
            }
            if (empty($end)) {
                $jadwalErrors["jadwal_{$day}"] = "Jam tutup wajib diisi untuk hari {$day} yang berstatus Buka.";
                continue;
            }
            if (strtotime($end) <= strtotime($start)) {
                $jadwalErrors["jadwal_{$day}"] = "Jam tutup tidak boleh sama atau lebih awal dari jam buka untuk hari {$day}.";
            }
        }

        if (!empty($jadwalErrors)) {
            return back()->withErrors($jadwalErrors)->withInput();
        }

        $data = $request->except(['jadwal', 'gambar', 'is_delivery']); // Handle special fields manually

        $bukaDays = [];
        $jamStrings = [];
        $daysOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        
        foreach ($daysOrder as $day) {
            if (isset($jadwal[$day]['buka'])) {
                $bukaDays[] = $day;
                $start = $jadwal[$day]['start'] ?? '00:00';
                $end = $jadwal[$day]['end'] ?? '00:00';
                $jamStrings[] = "$day: $start - $end";
            }
        }

        $data['hari_operasional'] = implode(', ', $bukaDays);
        if (empty($data['hari_operasional'])) $data['hari_operasional'] = 'Tutup Sementara';

        $uniqueTimes = [];
        foreach ($daysOrder as $day) {
            if (isset($jadwal[$day]['buka'])) {
                $start = $jadwal[$day]['start'] ?? '00:00';
                $end = $jadwal[$day]['end'] ?? '00:00';
                $uniqueTimes[] = "$start - $end";
            }
        }
        $uniqueTimes = array_unique($uniqueTimes);

        if (count($uniqueTimes) === 1 && count($bukaDays) > 0) {
            if (count($bukaDays) == 7) {
                $data['jam_operasional'] = "Setiap Hari: " . $uniqueTimes[0];
            } else {
                $data['jam_operasional'] = implode(', ', $bukaDays) . ": " . $uniqueTimes[0];
            }
        } else {
             $data['jam_operasional'] = implode("\n", $jamStrings);
        }
        if (empty($bukaDays)) $data['jam_operasional'] = '-';

        $data['is_delivery'] = $request->has('is_delivery');
        if ($request->file('gambar')) {
            if ($umkm->gambar) Storage::disk('public')->delete($umkm->gambar);
            $data['gambar'] = $request->file('gambar')->store('umkm', 'public');
        }
        $umkm->update($data);
        return redirect()->route('admin.umkms.index');
    }

    //hapus umkm
    public function destroy($id) {
        Umkm::destroy($id);
        return back();
    }
}
