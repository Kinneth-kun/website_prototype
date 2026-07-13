<?php

namespace Tests\Feature;

use App\Mail\AdminLoginOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CmsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_public_tenant_directory_is_database_driven(): void
    {
        $response = $this->getJson('/api/content/tenants')->assertOk();
        $this->assertGreaterThan(100, count($response->json()));
    }

    public function test_admin_can_login_and_access_cms(): void
    {
        Mail::fake();
        $user=User::create(['name'=>'Test Administrator','email'=>'admin@example.test','password'=>Hash::make('Strong-test-password-123!'),'role'=>'super_admin','status'=>'active']);
        $login = $this->postJson('/api/admin/login', ['email'=>$user->email,'password'=>'Strong-test-password-123!'])
            ->assertOk()->assertJson(['otp_required'=>true]);
        $code = null;
        Mail::assertQueued(AdminLoginOtp::class, function (AdminLoginOtp $mail) use (&$code) { $code = $mail->code; return true; });
        $verification = $this->postJson('/api/admin/verify-otp', ['challenge_id'=>$login->json('challenge_id'),'code'=>$code])
            ->assertOk()->assertJsonStructure(['token','user']);
        $this->withToken($verification->json('token'))->getJson('/api/admin/tenants')->assertOk()->assertJsonStructure(['data']);
        $this->postJson('/api/admin/verify-otp', ['challenge_id'=>$login->json('challenge_id'),'code'=>$code])->assertUnprocessable();
    }

    public function test_public_inquiry_is_stored(): void
    {
        $this->postJson('/api/inquiries', ['inquiry_type'=>'Leasing','name'=>'Sample User','email'=>'user@example.com','message'=>'I would like to ask about an available retail space.'])->assertCreated()->assertJsonStructure(['reference_number']);
        $this->assertDatabaseHas('inquiries', ['email'=>'user@example.com','status'=>'new']);
    }

    public function test_admin_can_upload_managed_media(): void
    {
        Storage::fake('public'); $token=Str::random(80);
        $user=User::create(['name'=>'Media Admin','email'=>'media@example.test','password'=>Hash::make('password'),'role'=>'super_admin','status'=>'active']);
        DB::table('admin_sessions')->insert(['id'=>(string)Str::uuid(),'user_id'=>$user->id,'token_hash'=>hash('sha256',$token),'expires_at'=>now()->addHour(),'created_at'=>now(),'updated_at'=>now()]);
        $response=$this->withToken($token)->post('/api/admin/media',['file'=>UploadedFile::fake()->image('mall.jpg',1200,800),'alt_text'=>'Mall interior'],['Accept'=>'application/json']);
        $response->assertCreated()->assertJsonPath('alt_text','Mall interior');
        Storage::disk('public')->assertExists($response->json('path'));
    }

    public function test_event_end_date_must_follow_start_date(): void
    {
        $token=Str::random(80); $user=User::create(['name'=>'Editor','email'=>'editor@example.test','password'=>Hash::make('password'),'role'=>'editor','status'=>'active']);
        DB::table('admin_sessions')->insert(['id'=>(string)Str::uuid(),'user_id'=>$user->id,'token_hash'=>hash('sha256',$token),'expires_at'=>now()->addHour(),'created_at'=>now(),'updated_at'=>now()]);
        $this->withToken($token)->postJson('/api/admin/events',['title'=>'Invalid event','start_datetime'=>'2026-08-10 10:00:00','end_datetime'=>'2026-08-09 10:00:00'])->assertUnprocessable();
    }
}
