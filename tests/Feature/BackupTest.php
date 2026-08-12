<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_backups_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($admin)->get('/backups');

        $response->assertStatus(200);
        $response->assertSee('Database Backups');
    }

    public function test_non_admin_cannot_access_backups_page(): void
    {
        $pharmacist = User::factory()->create(['role' => 'pharmacist', 'is_active' => true]);

        $response = $this->actingAs($pharmacist)->get('/backups');

        $response->assertStatus(403);
    }

    public function test_admin_can_generate_and_download_backup(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($admin)->post('/backups');

        $response->assertRedirect('/backups');
        $response->assertSessionHas('success');

        $files = Storage::files('backups');
        $this->assertNotEmpty($files);

        $filename = basename($files[0]);

        $downloadResponse = $this->actingAs($admin)->get("/backups/{$filename}/download");
        $downloadResponse->assertStatus(200);
    }
}
