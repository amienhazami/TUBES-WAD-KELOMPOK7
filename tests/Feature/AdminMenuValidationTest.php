<?php

namespace Tests\Feature;

use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminMenuValidationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $umkm;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // Create a UMKM with all required fields
        $this->umkm = Umkm::create([
            'nama_umkm' => 'Test UMKM',
            'no_whatsapp' => '6281234567890',
            'deskripsi' => 'Test Deskripsi',
            'alamat' => 'Test Alamat',
            'koordinat' => 'https://maps.google.com/test',
            'kategori' => ['Makanan Berat'],
            'jam_operasional' => 'Setiap Hari: 08:00 - 22:00',
        ]);
    }

    public function test_add_menu_with_invalid_price(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.umkm.menus.store', $this->umkm->id), [
                'nama_menu' => 'Ayam Penyet',
                'harga' => 'invalid_price',
                'kategori' => 'Makanan Berat',
                'deskripsi' => 'Enak sekali',
            ]);

        $response->assertSessionHasErrors(['harga']);
        $errors = session('errors')->get('harga');
        $this->assertContains('Harga harus berupa angka.', $errors);
    }

    public function test_add_menu_with_invalid_image_format(): void
    {
        $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.umkm.menus.store', $this->umkm->id), [
                'nama_menu' => 'Ayam Penyet',
                'harga' => '15000',
                'kategori' => 'Makanan Berat',
                'deskripsi' => 'Enak sekali',
                'gambar' => $file,
            ]);

        $response->assertSessionHasErrors(['gambar']);
        $errors = session('errors')->get('gambar');
        $this->assertContains('Format file tidak didukung', $errors);
    }
}
