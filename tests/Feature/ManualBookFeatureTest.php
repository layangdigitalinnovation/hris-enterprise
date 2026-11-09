<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualBookFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'superadmin@test.com',
        ]);

        $this->employee = User::factory()->create([
            'role' => 'employee',
            'email' => 'emp@test.com',
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('manual-book.index'))
            ->assertRedirect(route('login'));

        $this->get('/panduan')
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_manual_book(): void
    {
        // 1. Employee access
        $this->actingAs($this->employee)
            ->get(route('manual-book.index'))
            ->assertStatus(200)
            ->assertSee('Pusat Dokumentasi & Manual Book HRIS Pro')
            ->assertSee('1. Presensi, GPS Geofencing')
            ->assertSee('2. Cuti, Izin Sakit')
            ->assertSee('Matriks Perbandingan Hak Akses');

        // 2. Super Admin access via /panduan alias
        $this->actingAs($this->superAdmin)
            ->get('/panduan')
            ->assertStatus(200)
            ->assertSee('Matriks Perbandingan Hak Akses');
    }

    public function test_arabic_locale_displays_translated_content(): void
    {
        app()->setLocale('ar');

        $this->actingAs($this->employee)
            ->get(route('manual-book.index'))
            ->assertStatus(200)
            ->assertSee('مركز التوثيق ودليل تشغيل نظام الموارد البشرية')
            ->assertSee('1. الحضور والانصراف')
            ->assertSee('المادة 107 من نظام العمل السعودي')
            ->assertSee('مصفوفة مقارنة الصلاحيات لجميع الأدوار')
            ->assertSee('الأسئلة الشائعة وحلول المشكلات السريعة');
    }

    public function test_authenticated_user_can_download_manual_book_pdf(): void
    {
        $response = $this->actingAs($this->employee)
            ->get(route('manual-book.pdf'));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type', ''), 'pdf'));

        // Test preview mode via /panduan/pdf
        $previewResponse = $this->actingAs($this->superAdmin)
            ->get('/panduan/pdf?preview=1');

        $previewResponse->assertStatus(200);
        $this->assertTrue(str_contains($previewResponse->headers->get('content-type', ''), 'pdf'));
    }

    public function test_arabic_pdf_download_generates_arabic_file(): void
    {
        $response = $this->actingAs($this->employee)
            ->get(route('manual-book.pdf', ['lang' => 'ar']));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type', ''), 'pdf'));
        $contentDisposition = $response->headers->get('content-disposition', '');
        $this->assertTrue(str_contains($contentDisposition, 'Buku_Panduan_HRIS_AR_'));
    }
}

