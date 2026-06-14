<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Umkm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminMenuController extends Controller
{
    //menampilkan menu
    public function index(Request $request, $umkmId)
    {
        $umkm = Umkm::findOrFail($umkmId);
        $search = $request->query('search');

        $query = $umkm->menus();
        if ($search) {
            $query->where('nama_menu', 'like', '%' . $search . '%');
        }

        $menus = $query->latest()->get();
        return view('admin.umkm.menus.index', compact('umkm', 'menus'));
    }

    //menambah menu baru
    public function store(Request $request, $umkmId)
    {
        $umkm = Umkm::findOrFail($umkmId);
        
        $request->merge(['harga' => str_replace('.', '', $request->harga)]);

        $request->validate([
            'nama_menu' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|string|in:Makanan Berat,Makanan Ringan,Minuman',
            'gambar' => 'nullable|image|max:2048', 
        ]);

        $data = $request->only(['nama_menu', 'harga', 'deskripsi', 'kategori']);
        $data['umkm_id'] = $umkm->id;

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('menus', 'public');
        }

        Menu::create($data);

        return back()->with('success', 'Menu berhasil ditambahkan!');
    }

    //update menu
    public function update(Request $request, $umkmId, $menuId)
    {
        $menu = Menu::where('umkm_id', $umkmId)->findOrFail($menuId);

        $request->merge(['harga' => str_replace('.', '', $request->harga)]);

        $request->validate([
            'nama_menu' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|string|in:Makanan Berat,Makanan Ringan,Minuman',
            'gambar' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['nama_menu', 'harga', 'deskripsi', 'kategori']);

        if ($request->hasFile('gambar')) {
            if ($menu->gambar) {
                Storage::disk('public')->delete($menu->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('menus', 'public');
        }

        $menu->update($data);

        return back()->with('success', 'Menu berhasil diperbarui!');
    }

    //hapus menu
    public function destroy($umkmId, $menuId)
    {
        $menu = Menu::where('umkm_id', $umkmId)->findOrFail($menuId);
        
        if ($menu->gambar) {
            Storage::disk('public')->delete($menu->gambar);
        }
        
        $menu->delete();

        return back()->with('success', 'Menu berhasil dihapus!');
    }
}
