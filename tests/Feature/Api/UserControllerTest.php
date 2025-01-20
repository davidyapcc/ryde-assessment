<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\Sanctum;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
    }

    public function test_can_list_users(): void
    {
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/users');

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'dob',
                        'address',
                        'description',
                        'createdAt',
                        'updatedAt'
                    ]
                ],
                'meta' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page'
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next'
                ]
            ]);
    }

    public function test_can_create_user(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'dob' => '1990-01-01',
            'address' => '123 Test Street',
            'description' => 'Test description'
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertCreated()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'dob',
                    'address',
                    'description',
                    'createdAt',
                    'updatedAt'
                ]
            ]);
    }

    public function test_can_show_user(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'dob',
                    'address',
                    'description',
                    'createdAt',
                    'updatedAt'
                ]
            ]);
    }

    public function test_can_update_user(): void
    {
        $user = User::factory()->create();
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'dob' => '1990-01-01',
            'address' => '456 Updated Street'
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updateData);

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'dob',
                    'address',
                    'description',
                    'createdAt',
                    'updatedAt'
                ]
            ]);
    }

    public function test_can_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->postJson('/api/users', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password', 'dob', 'address']);
    }

    public function test_validates_date_format(): void
    {
        $user = User::factory()->create();
        $response = $this->putJson("/api/users/{$user->id}", [
            'dob' => '01-01-1990'
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['dob']);
    }

    public function test_validates_email_format(): void
    {
        $response = $this->postJson('/api/users', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'dob' => '1990-01-01',
            'address' => '123 Test St'
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_validates_unique_email(): void
    {
        $existingUser = User::factory()->create();

        $response = $this->postJson('/api/users', [
            'name' => 'Test User',
            'email' => $existingUser->email,
            'password' => 'password123',
            'dob' => '1990-01-01',
            'address' => '123 Test St'
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_can_create_user_without_description(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'dob' => '1990-01-01',
            'address' => '123 Test Street'
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertCreated()
            ->assertJson([
                'status' => 'success',
                'message' => 'User created successfully',
                'data' => [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'dob' => $userData['dob'],
                    'address' => $userData['address'],
                    'description' => null
                ]
            ]);
    }

    public function test_list_users_returns_in_correct_order(): void
    {
        // Clear existing users from setUp
        User::query()->delete();

        $olderUser = User::factory()->create([
            'created_at' => now()->subDay()
        ]);

        $newerUser = User::factory()->create([
            'created_at' => now()
        ]);

        $response = $this->getJson('/api/users');

        $response->assertOk();
        $responseData = $response->json();

        $this->assertEquals($newerUser->id, $responseData['data'][0]['id']);
        $this->assertEquals($olderUser->id, $responseData['data'][1]['id']);
    }

    public function test_custom_per_page_parameter(): void
    {
        User::factory()->count(5)->create();

        $response = $this->getJson('/api/users?per_page=2');

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 6); // 5 created here + 1 from setUp
    }

    public function test_pagination_works_correctly(): void
    {
        User::factory()->count(20)->create();

        $response = $this->getJson('/api/users?page=2');

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data',
                'meta' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page'
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next'
                ]
            ]);
    }

    public function test_partial_update_preserves_existing_data(): void
    {
        $user = User::factory()->create();
        $originalData = [
            'email' => $user->email,
            'dob' => $user->dob->format('Y-m-d'),
            'address' => $user->address
        ];

        $response = $this->putJson("/api/users/{$user->id}", [
            'name' => 'Updated Name'
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'User updated successfully',
                'data' => [
                    'name' => 'Updated Name',
                    'email' => $originalData['email'],
                    'dob' => $originalData['dob'],
                    'address' => $originalData['address']
                ]
            ]);
    }

    public function test_validates_empty_strings(): void
    {
        $response = $this->postJson('/api/users', [
            'name' => '',
            'email' => '',
            'password' => '',
            'dob' => '',
            'address' => ''
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password', 'dob', 'address']);
    }

    public function test_handles_invalid_json_request(): void
    {
        $response = $this->json('POST', '/api/users', ['invalid' => true], [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertStatus(422);
    }

    public function test_returns_404_for_non_existent_user(): void
    {
        $response = $this->getJson('/api/users/999');

        $response->assertNotFound();
    }

    public function test_validates_string_length_for_name(): void
    {
        $response = $this->postJson('/api/users', [
            'name' => str_repeat('a', 256),
            'email' => 'john@example.com',
            'password' => 'password123',
            'dob' => '1990-01-01',
            'address' => '123 Test Street'
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_validates_future_date_for_dob(): void
    {
        $response = $this->postJson('/api/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'dob' => date('Y-m-d', strtotime('+1 day')),
            'address' => '123 Test Street'
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['dob']);
    }
}
