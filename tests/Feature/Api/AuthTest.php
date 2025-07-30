<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password@123'
        ]);

        $response->assertStatus(201)->assertJsonStructure(['token']);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('password')
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)->assertJsonStructure(['token']);
    }

    public function test_user_can_request_password_reset_link()
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
        ]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_unauthorized_access()
    {
        $response = $this->getJson('/api/v1/unauthorized');

        $response->assertStatus(401)
                 ->assertJson(['status' => false, 'error' => 'Unauthorized']);
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v1/logout');

        $response->assertStatus(200);
    }

    public function test_user_can_logout_all_devices()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v1/logout-all-devices');

        $response->assertStatus(200);
    }
}
