<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_settings_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/settings');

        $response->assertStatus(200);
        $response->assertSee('System Settings');
    }

    public function test_non_admin_cannot_access_settings_page(): void
    {
        $user = User::factory()->create(['role' => 'pharmacist']);

        $response = $this->actingAs($user)->get('/settings');

        $response->assertStatus(403);
    }

    public function test_admin_can_update_settings_and_upload_logo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $logo = UploadedFile::fake()->create('custom_logo.png', 10, 'image/png');

        $response = $this->actingAs($admin)->post('/settings', [
            'app_name' => 'Custom Pharmacy Care',
            'currency_symbol' => 'USD',
            'system_email' => 'contact@custompharma.com',
            'system_phone' => '123456789',
            'system_address' => '456 Innovation Way',
            'app_logo' => $logo,
        ]);

        $response->assertRedirect('/settings');
        $this->assertEquals('Custom Pharmacy Care', setting('app_name'));
        $this->assertEquals('USD', setting('currency_symbol'));
        $this->assertNotNull(setting('app_logo'));

        Storage::disk('public')->assertExists(setting('app_logo'));
    }
}
