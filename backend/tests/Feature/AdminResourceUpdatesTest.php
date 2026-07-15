<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminResourceUpdatesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->seed();
    }

    public function test_leasing_schema_contains_the_new_business_fields(): void
    {
        $this->assertTrue(Schema::hasColumns('leasing_spaces', [
            'branch_id',
            'unit_code',
            'unit_name',
            'floor_level',
            'location_description',
            'floor_area_sqm',
            'base_rate_sqm',
            'cusa_rate_sqm',
            'ads_fee_sqm',
            'is_vatable',
            'status',
        ]));
    }

    public function test_admin_can_create_and_update_a_leasing_space_with_legacy_fields_synchronized(): void
    {
        $this->authenticateAdmin();

        $response = $this->postJson('/api/admin/leasing_spaces', [
            'branch_id' => 1,
            'unit_code' => 'GF-101',
            'unit_name' => 'Garden Retail Unit',
            'floor_level' => 'Ground Floor',
            'location_description' => 'Near the main entrance.',
            'floor_area_sqm' => 85.50,
            'base_rate_sqm' => 1400,
            'cusa_rate_sqm' => 180,
            'ads_fee_sqm' => 25,
            'is_vatable' => true,
            'status' => 'under_maintenance',
        ])->assertCreated();

        $id = $response->json('id');
        $this->assertDatabaseHas('leasing_spaces', [
            'id' => $id,
            'unit_name' => 'Garden Retail Unit',
            'title' => 'Garden Retail Unit',
            'unit_code' => 'GF-101',
            'unit_number' => 'GF-101',
            'floor_area_sqm' => 85.50,
            'area_sqm' => 85.50,
            'status' => 'under_maintenance',
            'availability_status' => 'under_maintenance',
        ]);

        $this->putJson('/api/admin/leasing_spaces/'.$id, [
            'unit_name' => 'Garden Corner Unit',
            'status' => 'reserved',
        ])->assertOk()->assertJson([
            'unit_name' => 'Garden Corner Unit',
            'title' => 'Garden Corner Unit',
            'status' => 'reserved',
            'availability_status' => 'reserved',
        ]);

        $this->putJson('/api/admin/leasing_spaces/'.$id, ['status' => 'not-a-status'])
            ->assertUnprocessable();
    }

    public function test_admin_sort_filters_and_public_alphabetical_defaults_are_applied(): void
    {
        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Aardvark QA',
            'slug' => 'aardvark-qa',
            'display_order' => 999,
            'is_active' => true,
            'created_at' => '2020-01-01 00:00:00',
            'updated_at' => now(),
        ]);
        DB::table('categories')->insert([
            'name' => 'Zyzzyva QA',
            'slug' => 'zyzzyva-qa',
            'display_order' => 0,
            'is_active' => true,
            'created_at' => '2030-01-01 00:00:00',
            'updated_at' => now(),
        ]);
        DB::table('tenants')->insert([
            [
                'name' => 'Aardvark Tenant QA',
                'slug' => 'aardvark-tenant-qa',
                'category_id' => $categoryId,
                'status' => 'active',
                'display_order' => 999,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Zyzzyva Tenant QA',
                'slug' => 'zyzzyva-tenant-qa',
                'category_id' => $categoryId,
                'status' => 'active',
                'display_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $publicCategories = collect($this->getJson('/api/content/categories')->assertOk()->json())->pluck('name');
        $publicTenants = collect($this->getJson('/api/content/tenants')->assertOk()->json())->pluck('name');
        $this->assertLessThan($publicCategories->search('Zyzzyva QA'), $publicCategories->search('Aardvark QA'));
        $this->assertLessThan($publicTenants->search('Zyzzyva Tenant QA'), $publicTenants->search('Aardvark Tenant QA'));

        $this->authenticateAdmin();
        $this->getJson('/api/admin/categories?sort=az&per_page=100')
            ->assertOk()->assertJsonPath('data.0.name', 'Aardvark QA');
        $this->getJson('/api/admin/categories?sort=za&per_page=100')
            ->assertOk()->assertJsonPath('data.0.name', 'Zyzzyva QA');
        $this->getJson('/api/admin/categories?sort=oldest&per_page=100')
            ->assertOk()->assertJsonPath('data.0.name', 'Aardvark QA');
        $this->getJson('/api/admin/categories?sort=newest&per_page=100')
            ->assertOk()->assertJsonPath('data.0.name', 'Zyzzyva QA');
    }

    private function authenticateAdmin(): void
    {
        $token = Str::random(80);
        $user = User::create([
            'name' => 'Resource Administrator',
            'email' => Str::uuid().'@example.test',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'status' => 'active',
        ]);
        DB::table('admin_sessions')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHour(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->withToken($token);
    }
}
