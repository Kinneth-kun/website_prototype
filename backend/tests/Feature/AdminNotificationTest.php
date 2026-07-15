<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_public_inquiry_notifies_each_active_administrator(): void
    {
        [$firstAdmin, $firstToken] = $this->createAdministrator('first-admin@example.test');
        [$secondAdmin] = $this->createAdministrator('second-admin@example.test', 'editor');
        $inactiveAdmin = User::create([
            'name' => 'Inactive Admin',
            'email' => 'inactive-admin@example.test',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'status' => 'inactive',
        ]);

        $response = $this->postJson('/api/inquiries', [
            'inquiry_type' => 'Leasing',
            'name' => 'Sample Customer',
            'email' => 'customer@example.test',
            'subject' => 'Retail space availability',
            'message' => 'I would like to ask about an available retail space.',
        ])->assertCreated()->assertJsonStructure(['message', 'reference_number']);

        $this->assertDatabaseHas('inquiries', [
            'reference_number' => $response->json('reference_number'),
            'email' => 'customer@example.test',
        ]);
        $this->assertDatabaseCount('notifications', 2);
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $firstAdmin->id,
            'read_at' => null,
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $secondAdmin->id,
            'read_at' => null,
        ]);
        $this->assertDatabaseMissing('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $inactiveAdmin->id,
        ]);

        $this->withHeader('Authorization', 'Bearer '.$firstToken)
            ->getJson('/api/admin/notifications')
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.type', 'new_inquiry')
            ->assertJsonPath('data.0.title', 'New inquiry from Sample Customer')
            ->assertJsonPath('data.0.data.reference_number', $response->json('reference_number'))
            ->assertJsonPath('data.0.is_read', false);
    }

    public function test_administrator_can_mark_own_notifications_read_but_not_another_users(): void
    {
        [$firstAdmin, $firstToken] = $this->createAdministrator('first-admin@example.test');
        [$secondAdmin] = $this->createAdministrator('second-admin@example.test');

        $this->postJson('/api/inquiries', [
            'inquiry_type' => 'Events',
            'name' => 'Customer One',
            'email' => 'one@example.test',
            'message' => 'What events are currently available at the mall?',
        ])->assertCreated();

        $firstNotification = DB::table('notifications')->where('notifiable_id', $firstAdmin->id)->value('id');
        $secondNotification = DB::table('notifications')->where('notifiable_id', $secondAdmin->id)->value('id');

        $this->withHeader('Authorization', 'Bearer '.$firstToken)
            ->putJson('/api/admin/notifications/'.$secondNotification.'/read')
            ->assertNotFound();

        $this->withHeader('Authorization', 'Bearer '.$firstToken)
            ->putJson('/api/admin/notifications/'.$firstNotification.'/read')
            ->assertOk()
            ->assertJsonPath('unread_count', 0)
            ->assertJsonPath('notification.is_read', true);

        $this->assertDatabaseHas('notifications', ['id' => $firstNotification]);
        $this->assertNotNull(DB::table('notifications')->where('id', $firstNotification)->value('read_at'));
        $this->assertNull(DB::table('notifications')->where('id', $secondNotification)->value('read_at'));
    }

    public function test_administrator_can_mark_all_notifications_read(): void
    {
        [$admin, $token] = $this->createAdministrator('admin@example.test');

        foreach (['Leasing', 'Services'] as $index => $type) {
            $this->postJson('/api/inquiries', [
                'inquiry_type' => $type,
                'name' => 'Customer '.($index + 1),
                'email' => 'customer'.($index + 1).'@example.test',
                'message' => 'This is a valid customer inquiry message.',
            ])->assertCreated();
        }

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/admin/notifications/read-all')
            ->assertOk()
            ->assertJsonPath('updated_count', 2)
            ->assertJsonPath('unread_count', 0);

        $this->assertSame(0, DB::table('notifications')
            ->where('notifiable_id', $admin->id)
            ->whereNull('read_at')
            ->count());
    }

    public function test_notification_endpoints_require_an_authenticated_admin_session(): void
    {
        $this->getJson('/api/admin/notifications')->assertUnauthorized();
        $this->putJson('/api/admin/notifications/read-all')->assertUnauthorized();
        $this->putJson('/api/admin/notifications/'.Str::uuid().'/read')->assertUnauthorized();
    }

    private function createAdministrator(string $email, string $role = 'super_admin'): array
    {
        $user = User::create([
            'name' => 'Website Administrator',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => $role,
            'status' => 'active',
        ]);
        $token = Str::random(80);

        DB::table('admin_sessions')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHour(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$user, $token];
    }
}
