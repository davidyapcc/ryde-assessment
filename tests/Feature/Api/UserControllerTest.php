<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
}
