<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'dob' => '1990-01-01',
            'address' => '123 Test Street',
            'description' => 'Test description'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertCreated()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'dob',
                        'address',
                        'description',
                        'createdAt',
                        'updatedAt'
                    ],
                    'token'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'name' => $userData['name']
        ]);
    }

    public function test_user_cannot_register_with_existing_email(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'john@example.com'
        ]);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'dob' => '1990-01-01',
            'address' => '123 Test Street'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email'
                    ],
                    'token'
                ]
            ]);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'wrongpassword'
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertUnauthorized()
            ->assertJson([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ]);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout');

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Successfully logged out'
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_validates_registration_fields(): void
    {
        $response = $this->postJson('/api/auth/register', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password',
                'dob',
                'address'
            ]);
    }

    public function test_validates_login_fields(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email',
                'password'
            ]);
    }

    public function test_password_must_be_at_least_8_characters(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
            'dob' => '1990-01-01',
            'address' => '123 Test Street'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }
}
