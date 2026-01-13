<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\GeneralSetting;

class GeneralSettingImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Ensure storage fake
        Storage::fake('public');
    }

    /** @test */
    public function admin_can_upload_wallet_header_images_and_toggle_slideshow()
    {
        $this->withoutMiddleware();

        $file1 = UploadedFile::fake()->image('wallet1.jpg', 1200, 800);
        $file2 = UploadedFile::fake()->image('wallet2.jpg', 1200, 800);

        $response = $this->post(route('setting.update'), [
            'site_name' => 'Test',
            'cur_text' => 'USD',
            'cur_sym' => '$',
            'registration_bonus' => 0,
            'bt_fixed' => 0,
            'bt_percent' => 0,
            'default_plan' => 0,
            'timezone' => "'UTC'",
            'wallet_images' => [$file1, $file2],
            'wallet_header_slideshow' => 1
        ]);

        $response->assertStatus(302);

        $general = GeneralSetting::first();
        $this->assertNotNull($general);
        $this->assertEquals(1, $general->wallet_header_slideshow);
        $this->assertIsArray($general->wallet_images);
        $this->assertCount(2, $general->wallet_images);
    }
}
