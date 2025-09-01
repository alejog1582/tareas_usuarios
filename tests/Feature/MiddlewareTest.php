<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private $validToken;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar el token válido
        $this->validToken = config('app.api_token');
        
        // Crear un usuario de prueba
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);
    }

    /**
     * Test que las rutas GET no requieren autenticación
     */
    public function test_get_routes_do_not_require_authentication(): void
    {
        // Test GET /api/users
        $response = $this->getJson('/api/users');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
            'message'
        ]);

        // Test GET /api/users/{id}/tasks
        $response = $this->getJson("/api/users/{$this->user->id}/tasks");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'user',
                'tasks'
            ],
            'message'
        ]);
    }

    /**
     * Test que las rutas POST requieren token válido
     */
    public function test_post_routes_require_valid_token(): void
    {
        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'user_id' => $this->user->id
        ];

        // Test sin token
        $response = $this->postJson('/api/tasks', $taskData);
        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Authorization header is required'
        ]);

        // Test con token inválido
        $response = $this->postJson('/api/tasks', $taskData, [
            'Authorization' => 'Bearer invalid_token'
        ]);
        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid API token'
        ]);

        // Test con token válido
        $response = $this->postJson('/api/tasks', $taskData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);
        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Task created successfully'
        ]);
    }

    /**
     * Test que las rutas PUT requieren token válido
     */
    public function test_put_routes_require_valid_token(): void
    {
        // Crear una tarea primero
        $task = $this->user->tasks()->create([
            'title' => 'Original Task',
            'description' => 'Original Description',
            'status' => 'pending'
        ]);

        $updateData = [
            'title' => 'Updated Task',
            'status' => 'in_progress'
        ];

        // Test sin token
        $response = $this->putJson("/api/tasks/{$task->id}", $updateData);
        $response->assertStatus(401);

        // Test con token inválido
        $response = $this->putJson("/api/tasks/{$task->id}", $updateData, [
            'Authorization' => 'Bearer invalid_token'
        ]);
        $response->assertStatus(401);

        // Test con token válido
        $response = $this->putJson("/api/tasks/{$task->id}", $updateData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Task updated successfully'
        ]);
    }

    /**
     * Test que las rutas DELETE requieren token válido
     */
    public function test_delete_routes_require_valid_token(): void
    {
        // Crear una tarea primero
        $task = $this->user->tasks()->create([
            'title' => 'Task to Delete',
            'description' => 'This task will be deleted',
            'status' => 'pending'
        ]);

        // Test sin token
        $response = $this->deleteJson("/api/tasks/{$task->id}");
        $response->assertStatus(401);

        // Test con token inválido
        $response = $this->deleteJson("/api/tasks/{$task->id}", [], [
            'Authorization' => 'Bearer invalid_token'
        ]);
        $response->assertStatus(401);

        // Test con token válido
        $response = $this->deleteJson("/api/tasks/{$task->id}", [], [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Task deleted successfully'
        ]);
    }

    /**
     * Test que el middleware acepta diferentes formatos de Authorization header
     */
    public function test_middleware_accepts_different_authorization_formats(): void
    {
        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'user_id' => $this->user->id
        ];

        // Test con formato "Bearer {token}"
        $response = $this->postJson('/api/tasks', $taskData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);
        $response->assertStatus(201);

        // Test con solo el token (sin Bearer)
        $response = $this->postJson('/api/tasks', $taskData, [
            'Authorization' => $this->validToken
        ]);
        $response->assertStatus(201);
    }
}
