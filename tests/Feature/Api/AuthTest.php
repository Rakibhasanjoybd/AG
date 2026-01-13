<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\Deposit;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWTFactory;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a default plan for testing
        Plan::create([
            'name' => 'Test Plan',
            'price' => 100,
            'daily_limit' => 50,
            'validity' => 30,
            'status' => 1,
        ]);
    }

    /** @test */
    public function it_can_register_a_new_user()
    {
        $response = $this->postJson('/api/auth/register', [
            'fullname' => 'John Doe',
            'mobile_code' => '+1',
            'mobile' => '1234567890',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'withdrawal_pin' => '1234',
            'country_code' => 'US',
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'fullname',
                            'username',
                            'email',
                            'mobile',
                            'balance',
                            'referral_code',
                        ],
                        'token'
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'mobile' => '+11234567890',
            'fullname' => 'John Doe',
            'status' => 1,
        ]);

        $this->assertDatabaseHas('transactions', [
            'remark' => 'registration_bonus',
            'trx_type' => '+',
        ]);
    }

    /** @test */
    public function it_validates_registration_data()
    {
        $response = $this->postJson('/api/auth/register', [
            'fullname' => '',
            'mobile' => '123',
            'password' => '123',
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['fullname', 'mobile', 'password']);
    }

    /** @test */
    public function it_prevents_duplicate_mobile_registration()
    {
        User::create([
            'fullname' => 'Existing User',
            'mobile' => '+11234567890',
            'email' => 'existing@test.com',
            'password' => Hash::make('password'),
            'withdrawal_pin' => Hash::make('1234'),
            'username' => 'user1234567890',
            'referral_code' => 'ABCDE',
        ]);

        $response = $this->postJson('/api/auth/register', [
            'fullname' => 'New User',
            'mobile_code' => '+1',
            'mobile' => '1234567890',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'withdrawal_pin' => '1234',
            'country_code' => 'US',
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['mobile']);
    }

    /** @test */
    public function it_can_login_with_valid_credentials()
    {
        $user = User::create([
            'fullname' => 'Test User',
            'mobile' => '+11234567890',
            'email' => 'test@test.com',
            'password' => Hash::make('Password123!'),
            'withdrawal_pin' => Hash::make('1234'),
            'username' => 'user1234567890',
            'referral_code' => 'ABCDE',
            'status' => 1,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'mobile' => '+11234567890',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'token',
                        'token_type',
                        'expires_in',
                        'user',
                    ]
                ]);
    }

    /** @test */
    public function it_cannot_login_with_invalid_credentials()
    {
        $user = User::create([
            'fullname' => 'Test User',
            'mobile' => '+11234567890',
            'email' => 'test@test.com',
            'password' => Hash::make('Password123!'),
            'withdrawal_pin' => Hash::make('1234'),
            'username' => 'user1234567890',
            'referral_code' => 'ABCDE',
            'status' => 1,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'mobile' => '+11234567890',
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Invalid credentials',
                ]);
    }

    /** @test */
    public function it_cannot_login_with_banned_account()
    {
        $user = User::create([
            'fullname' => 'Banned User',
            'mobile' => '+11234567890',
            'email' => 'banned@test.com',
            'password' => Hash::make('Password123!'),
            'withdrawal_pin' => Hash::make('1234'),
            'username' => 'user1234567890',
            'referral_code' => 'ABCDE',
            'status' => 0, // Banned
        ]);

        $response = $this->postJson('/api/auth/login', [
            'mobile' => '+11234567890',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'Your account has been banned',
                ]);
    }

    /** @test */
    public function it_can_logout()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
                        ->postJson('/api/auth/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Successfully logged out',
                ]);
    }

    /** @test */
    public function it_can_refresh_token()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
                        ->postJson('/api/auth/refresh');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'token',
                        'token_type',
                        'expires_in',
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_authenticated_user()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
                        ->getJson('/api/auth/me');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'fullname',
                        'username',
                        'email',
                        'mobile',
                        'balance',
                        'referral_code',
                    ]
                ]);
    }

    /** @test */
    public function it_requires_authentication_for_protected_routes()
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Unauthorized',
                ]);
    }
}
