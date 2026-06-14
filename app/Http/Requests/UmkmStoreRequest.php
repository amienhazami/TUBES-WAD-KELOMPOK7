<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UmkmStoreRequest extends FormRequest
{
    /**
     * Hanya admin yang boleh akses.
     */
    public function authorize(): bool
    {
        return true; // sudah dijaga oleh middleware role:admin di routes
    }

    /**
     * Validation rules sesuai Equivalence Partitioning (EP) Tambah UMKM Baru.
     */
    public function rules(): array
    {
        return [
            // ── EP-01: Nama UMKM ─────────────────────────────────────────────
            // VP : 2–100 karakter, huruf, angka & spasi
            // IP1: Kosong
            // IP2: Lebih dari 100 karakter
            'nama_umkm' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[\p{L}0-9\s\-\'\.&,()\/ ]+$/u', // huruf, angka, spasi & karakter umum
            ],

            // ── EP-02: Kategori ───────────────────────────────────────────────
            // VP : Minimal 1 checkbox dipilih (Makanan Berat / Makanan Ringan / Minuman)
            // IP1: Tidak ada checkbox yang dipilih
            'kategori'   => ['required', 'array', 'min:1'],
            'kategori.*' => ['in:Makanan Berat,Makanan Ringan,Minuman'],

            // ── EP-03: No WhatsApp ────────────────────────────────────────────
            // VP : Format angka saja (contoh: 6281234567890)
            // IP1: Kosong
            // IP2: Format tidak valid mengandung huruf (contoh: abcdefghij)
            'no_whatsapp' => [
                'required',
                'regex:/^[0-9]+$/',
            ],

            // ── EP-04: Deskripsi ──────────────────────────────────────────────
            // VP : 2–500 karakter, teks bebas
            // IP1: Kosong
            // IP2: Lebih dari 500 karakter
            'deskripsi' => ['required', 'string', 'min:2', 'max:500'],

            // ── EP-06: Alamat Lengkap ─────────────────────────────────────────
            // VP : 2–255 karakter, teks bebas
            // IP1: Kosong
            // IP2: Lebih dari 255 karakter
            'alamat' => ['required', 'string', 'min:2', 'max:255'],

            // ── EP-07: Link Google Maps ───────────────────────────────────────
            // VP : Format URL valid (https://maps.google.com/...) atau dikosongkan
            // IP1: Format bukan URL (tidak diawali https://)
            'koordinat' => [
                'nullable',
                'url',
                'regex:/^https:\/\//',  // wajib diawali https://
            ],

            // ── EP-08: Upload Gambar ──────────────────────────────────────────
            // VP : File jpg/jpeg/png, ukuran ≤ 2MB atau tidak diupload
            // IP1: Format file tidak didukung (misal .pdf, .gif)
            // IP2: Ukuran file > 2MB
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],

            // ── EP-09: Bisa Delivery ──────────────────────────────────────────
            // VP : Checkbox dicentang (Ya) atau tidak dicentang (Tidak) — keduanya valid
            'is_delivery' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Pesan error kustom yang jelas dan spesifik per field.
     */
    public function messages(): array
    {
        return [
            // Nama UMKM
            'nama_umkm.required'  => 'Nama UMKM tidak boleh kosong.',
            'nama_umkm.min'       => 'Nama UMKM minimal harus terdiri dari 2 karakter.',
            'nama_umkm.max'       => 'Nama UMKM tidak boleh melebihi 100 karakter.',
            'nama_umkm.regex'     => 'Nama UMKM hanya boleh mengandung huruf, angka, dan spasi.',

            // Kategori
            'kategori.required' => 'Harap pilih minimal satu kategori.',
            'kategori.min'      => 'Harap pilih minimal satu kategori.',
            'kategori.*.in'     => 'Kategori yang dipilih tidak valid.',

            // No WhatsApp
            'no_whatsapp.required' => 'No WhatsApp tidak boleh kosong.',
            'no_whatsapp.regex'    => 'No WhatsApp hanya boleh berisi angka (contoh: 6281234567890).',

            // Deskripsi
            'deskripsi.required' => 'Deskripsi tidak boleh kosong.',
            'deskripsi.min'      => 'Deskripsi minimal harus terdiri dari 2 karakter.',
            'deskripsi.max'      => 'Deskripsi tidak boleh melebihi 500 karakter.',

            // Alamat
            'alamat.required' => 'Alamat Lengkap tidak boleh kosong.',
            'alamat.min'      => 'Alamat Lengkap minimal harus terdiri dari 2 karakter.',
            'alamat.max'      => 'Alamat Lengkap tidak boleh melebihi 255 karakter.',

            // Link Google Maps
            'koordinat.url'   => 'Format Link Google Maps tidak valid — harap masukkan URL yang benar (contoh: https://maps.google.com/...).',
            'koordinat.regex' => 'Link Google Maps harus diawali dengan https:// (contoh: https://maps.google.com/...).',

            // Gambar
            'gambar.image'  => 'File yang diunggah harus berupa gambar.',
            'gambar.mimes'  => 'Format file tidak didukung. Harap upload file dengan format JPG, JPEG, atau PNG.',
            'gambar.max'    => 'Ukuran file terlalu besar. Maksimal ukuran file adalah 2MB.',
        ];
    }

    /**
     * Label atribut yang lebih ramah pengguna (untuk pesan error default).
     */
    public function attributes(): array
    {
        return [
            'nama_umkm'   => 'Nama UMKM',
            'kategori'    => 'Kategori',
            'no_whatsapp' => 'No WhatsApp',
            'deskripsi'   => 'Deskripsi',
            'alamat'      => 'Alamat Lengkap',
            'koordinat'   => 'Link Google Maps',
            'gambar'      => 'Gambar',
        ];
    }
}
