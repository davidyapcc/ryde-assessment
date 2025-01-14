<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_users(): void
    {
        $users = User::factory(3)->create();

        $response = $this->getJson('/api/users');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'dob',
                        'address',
                        'description',
                        'createdAt',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_can_create_user(): void
    {
        $userData = [
            'name' => 'John Doe',
            'dob' => '1990-01-01',
            'address' => '123 Test Street',
            'description' => 'Test description',
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'dob',
                    'address',
                    'description',
                    'createdAt',
                ],
            ])
            ->assertJson([
                'data' => [
                    'name' => $userData['name'],
                    'dob' => $userData['dob'],
                    'address' => $userData['address'],
                    'description' => $userData['description'],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'address' => $userData['address'],
            'description' => $userData['description'],
        ]);
    }

    public function test_can_show_user(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'dob',
                    'address',
                    'description',
                    'createdAt',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'dob' => $user->dob->format('Y-m-d'),
                    'address' => $user->address,
                    'description' => $user->description,
                ],
            ]);
    }

    public function test_can_update_user(): void
    {
        $user = User::factory()->create();
        $updateData = [
            'name' => 'Updated Name',
            'address' => 'Updated Address',
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updateData);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'dob',
                    'address',
                    'description',
                    'createdAt',
                ],
            ])
            ->assertJson([
                'data' => [
                    'name' => $updateData['name'],
                    'address' => $updateData['address'],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $updateData['name'],
            'address' => $updateData['address'],
        ]);
    }

    public function test_can_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_validates_required_fields_when_creating_user(): void
    {
        $response = $this->postJson('/api/users', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'dob', 'address']);
    }

    public function test_validates_date_format_for_dob(): void
    {
        $response = $this->postJson('/api/users', [
            'name' => 'John Doe',
            'dob' => 'invalid-date',
            'address' => '123 Test Street',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['dob']);
    }

    public function test_validates_future_date_for_dob(): void
    {
        $response = $this->postJson('/api/users', [
            'name' => 'John Doe',
            'dob' => now()->addDay()->format('Y-m-d'),
            'address' => '123 Test Street',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['dob']);
    }

    public function test_returns_404_for_non_existent_user(): void
    {
        $response = $this->getJson('/api/users/999');

        $response->assertNotFound()
            ->assertJson(['message' => 'No query results for model [App\\Models\\User] 999']);
    }

    public function test_validates_string_length_for_name(): void
    {
        $response = $this->postJson('/api/users', [
            'name' => str_repeat('a', 256), // Exceeds max length of 255
            'dob' => '1990-01-01',
            'address' => '123 Test Street',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_create_user_without_description(): void
    {
        $userData = [
            'name' => 'John Doe',
            'dob' => '1990-01-01',
            'address' => '123 Test Street',
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'name' => $userData['name'],
                    'dob' => $userData['dob'],
                    'address' => $userData['address'],
                    'description' => null,
                ],
            ]);
    }

    public function test_pagination_works_correctly(): void
    {
        User::factory(15)->create();

        $response = $this->getJson('/api/users?page=2');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'links',
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ])
            ->assertJson([
                'meta' => [
                    'current_page' => 2,
                ],
            ]);
    }

    public function test_partial_update_preserves_existing_data(): void
    {
        $user = User::factory()->create();
        $originalDob = $user->dob->format('Y-m-d');

        $updateData = [
            'name' => 'Updated Name',
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updateData);

        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'dob' => $originalDob,
            'address' => $user->address,
            'description' => $user->description,
        ]);
    }

    public function test_validates_empty_strings(): void
    {
        $response = $this->postJson('/api/users', [
            'name' => '',
            'dob' => '1990-01-01',
            'address' => '',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'address']);
    }

    public function test_handles_invalid_json_request(): void
    {
        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'Accept' => 'application/json',
        ];

        $response = $this->withHeaders($headers)
            ->post('/api/users', [], $headers);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'dob', 'address']);
    }

    public function test_list_users_returns_in_correct_order(): void
    {
        $olderUser = User::factory()->create([
            'created_at' => now()->subDays(1),
        ]);
        $newerUser = User::factory()->create([
            'created_at' => now(),
        ]);

        $response = $this->getJson('/api/users');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'id' => $newerUser->id,
                    ],
                    [
                        'id' => $olderUser->id,
                    ],
                ],
            ]);
    }

    public function test_update_with_invalid_date_format(): void
    {
        $user = User::factory()->create();

        $response = $this->putJson("/api/users/{$user->id}", [
            'dob' => '01-01-1990', // Invalid format, should be Y-m-d
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['dob']);
    }
}
