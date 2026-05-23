<?php

namespace App\Http\Controllers;
use App\Models\Umkm;
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
    public function store(Request $request) {
        $data = $request->validate([
            'nama_umkm' => 'required',
            'deskripsi' => 'required',
            'no_whatsapp' => 'required|regex:/^[0-9]+$/',
            'kategori' => 'required|array',
            'gambar' => 'image',
            'alamat' => 'required',
            'koordinat' => 'nullable',
        ], [
            'no_whatsapp.regex' => 'Nomor WhatsApp harus berupa angka saja tanpa huruf atau karakter lain.',
        ]);

        $jadwal = $request->input('jadwal', []);
        $errors = [];
        foreach ($jadwal as $day => $times) {
            if (isset($times['buka'])) {
                $start = $times['start'] ?? '';
                $end = $times['end'] ?? '';
                if (empty($start) || empty($end) || strtotime($start) >= strtotime($end)) {
                    $errors["jadwal_$day"] = "Jam tutup pada hari $day harus setelah jam buka.";
                }
            }
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

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
        if ($request->file('gambar')) $data['gambar'] = $request->file('gambar')->store('umkm', 'public');

        Umkm::create($data);
        return redirect()->route('admin.umkms.index');
    }

    //form edit umkm
    public function edit($id) {
        $umkm = Umkm::findOrFail($id);
        return view('admin.umkm.edit', compact('umkm'));
    }

    //update data umkm
    public function update(Request $request, $id) {
        $umkm = Umkm::findOrFail($id);

        $request->validate([
            'nama_umkm' => 'required',
            'deskripsi' => 'required',
            'no_whatsapp' => 'required|regex:/^[0-9]+$/',
            'kategori' => 'required|array',
            'gambar' => 'nullable|image',
            'alamat' => 'required',
            'koordinat' => 'nullable',
        ], [
            'no_whatsapp.regex' => 'Nomor WhatsApp harus berupa angka saja tanpa huruf atau karakter lain.',
        ]);

        $jadwal = $request->input('jadwal', []);
        $errors = [];
        foreach ($jadwal as $day => $times) {
            if (isset($times['buka'])) {
                $start = $times['start'] ?? '';
                $end = $times['end'] ?? '';
                if (empty($start) || empty($end) || strtotime($start) >= strtotime($end)) {
                    $errors["jadwal_$day"] = "Jam tutup pada hari $day harus setelah jam buka.";
                }
            }
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
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
