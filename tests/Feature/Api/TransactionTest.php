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
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $testPlan;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a default plan for testing
        $this->testPlan = Plan::create([
            'name' => 'Test Plan',
            'price' => 100,
            'daily_limit' => 50,
            'validity' => 30,
            'status' => 1,
        ]);

        // Create and authenticate user
        $this->user = User::create([
            'fullname' => 'Test User',
            'mobile' => '+11234567890',
            'email' => 'test@test.com',
            'password' => Hash::make('Password123!'),
            'withdrawal_pin' => Hash::make('1234'),
            'username' => 'user1234567890',
            'referral_code' => 'ABCDE',
            'balance' => 1000,
            'status' => 1,
            'plan_id' => $this->testPlan->id,
            'daily_limit' => 50,
            'expire_date' => now()->addDays(30),
        ]);

        $this->token = JWTAuth::fromUser($this->user);
    }

    /** @test */
    public function it_can_create_withdrawal_request()
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
                        ->postJson('/api/user/withdraw/store', [
                            'amount' => 100,
                            'method_id' => 1,
                            'wallet_number' => '1234567890123456',
                            'withdrawal_pin' => '1234',
                        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'withdrawal' => [
                            'id',
                            'amount',
                            'trx',
                            'status',
                        ]
                    ]
                ]);

        $this->assertDatabaseHas('withdrawals', [
            'user_id' => $this->user->id,
            'amount' => 100,
            'status' => 1, // pending
        ]);

        // Verify balance is locked but not yet deducted
        $this->user->refresh();
        $this->assertEquals(1000, $this->user->balance);
    }

    /** @test */
    public function it_prevents_withdrawal_with_insufficient_balance()
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
                        ->postJson('/api/user/withdraw/store', [
                            'amount' => 1500, // More than balance
                            'method_id' => 1,
                            'wallet_number' => '1234567890123456',
                            'withdrawal_pin' => '1234',
                        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Insufficient balance',
                ]);

        $this->assertDatabaseMissing('withdrawals', [
            'user_id' => $this->user->id,
            'amount' => 1500,
        ]);
    }

    /** @test */
    public function it_validates_withdrawal_pin()
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
                        ->postJson('/api/user/withdraw/store', [
                            'amount' => 100,
                            'method_id' => 1,
                            'wallet_number' => '1234567890123456',
                            'withdrawal_pin' => '9999', // Wrong PIN
                        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Invalid withdrawal PIN',
                ]);
    }

    /** @test */
    public function it_can_create_deposit_request()
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
                        ->postJson('/api/user/deposit/store', [
                            'amount' => 500,
                            'method_code' => 101,
                            'method_currency' => 'USD',
                        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'deposit' => [
                            'id',
                            'amount',
                            'trx',
                            'status',
                        ]
                    ]
                ]);

        $this->assertDatabaseHas('deposits', [
            'user_id' => $this->user->id,
            'amount' => 500,
            'status' => 1, // pending
        ]);
    }

    /** @test */
    public function it_can_purchase_plan()
    {
        $newPlan = Plan::create([
            'name' => 'Premium Plan',
            'price' => 500,
            'daily_limit' => 100,
            'validity' => 60,
            'status' => 1,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
                        ->postJson('/api/user/plan/purchase', [
                            'plan_id' => $newPlan->id,
                        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'transaction',
                        'user',
                    ]
                ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'amount' => 500,
            'trx_type' => '-',
            'remark' => 'plan_purchase',
        ]);

        // Verify user plan updated
        $this->user->refresh();
        $this->assertEquals(500, $this->user->balance);
        $this->assertEquals($newPlan->id, $this->user->plan_id);
        $this->assertEquals(100, $this->user->daily_limit);
    }

    /** @test */
    public function it_prevents_plan_purchase_with_insufficient_balance()
    {
        $expensivePlan = Plan::create([
            'name' => 'Expensive Plan',
            'price' => 2000,
            'daily_limit' => 200,
            'validity' => 90,
            'status' => 1,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
                        ->postJson('/api/user/plan/purchase', [
                            'plan_id' => $expensivePlan->id,
                        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Insufficient balance',
                ]);

        $this->assertDatabaseMissing('transactions', [
            'user_id' => $this->user->id,
            'amount' => 2000,
            'remark' => 'plan_purchase',
        ]);
    }

    /** @test */
    public function it_handles_concurrent_withdrawal_requests()
    {
        // Simulate concurrent withdrawal requests
        $firstResponse = $this->withHeader('Authorization', "Bearer $this->token")
                            ->postJson('/api/user/withdraw/store', [
                                'amount' => 800,
                                'method_id' => 1,
                                'wallet_number' => '1234567890123456',
                                'withdrawal_pin' => '1234',
                            ]);

        // Second request should fail due to insufficient balance after first lock
        $secondResponse = $this->withHeader('Authorization', "Bearer $this->token")
                             ->postJson('/api/user/withdraw/store', [
                                 'amount' => 500,
                                 'method_id' => 1,
                                 'wallet_number' => '1234567890123456',
                                 'withdrawal_pin' => '1234',
                             ]);

        // At least one should fail due to race condition protection
        $this->assertTrue(
            $firstResponse->getStatusCode() === 400 || $secondResponse->getStatusCode() === 400
        );
    }

    /** @test */
    public function it_can_get_transaction_history()
    {
        // Create some transactions
        Transaction::create([
            'user_id' => $this->user->id,
            'amount' => 100,
            'post_balance' => 900,
            'charge' => 0,
            'trx_type' => '-',
            'trx' => 'TXN123',
            'details' => 'Test withdrawal',
            'remark' => 'withdrawal',
        ]);

        Transaction::create([
            'user_id' => $this->user->id,
            'amount' => 50,
            'post_balance' => 950,
            'charge' => 0,
            'trx_type' => '+',
            'trx' => 'TXN124',
            'details' => 'Test deposit',
            'remark' => 'deposit',
        ]);

        $response = $this->withHeader('Authorization', "Bearer $this->token")
                        ->getJson('/api/user/transactions');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'transactions' => [
                            '*' => [
                                'id',
                                'amount',
                                'trx_type',
                                'details',
                                'created_at',
                            ]
                        ],
                        'pagination',
                    ]
                ]);

        $this->assertEquals(2, count($response->json('data.transactions')));
    }

    /** @test */
    public function it_validates_transaction_amount()
    {
        $response = $this->withHeader('Authorization', "Bearer $this->token")
                        ->postJson('/api/user/withdraw/store', [
                            'amount' => -100, // Negative amount
                            'method_id' => 1,
                            'wallet_number' => '1234567890123456',
                            'withdrawal_pin' => '1234',
                        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['amount']);
    }

    /** @test */
    public function it_respects_daily_withdrawal_limit()
    {
        // Set daily limit to 200
        $this->user->daily_limit = 200;
        $this->user->save();

        // First withdrawal within limit
        $firstResponse = $this->withHeader('Authorization', "Bearer $this->token")
                              ->postJson('/api/user/withdraw/store', [
                                  'amount' => 150,
                                  'method_id' => 1,
                                  'wallet_number' => '1234567890123456',
                                  'withdrawal_pin' => '1234',
                              ]);

        $firstResponse->assertStatus(200);

        // Second withdrawal exceeds daily limit
        $secondResponse = $this->withHeader('Authorization', "Bearer $this->token")
                               ->postJson('/api/user/withdraw/store', [
                                   'amount' => 100,
                                   'method_id' => 1,
                                   'wallet_number' => '1234567890123456',
                                   'withdrawal_pin' => '1234',
                               ]);

        $secondResponse->assertStatus(400)
                      ->assertJson([
                          'success' => false,
                          'message' => 'Daily withdrawal limit exceeded',
                      ]);
    }
}
