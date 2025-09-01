<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ValidationTest extends TestCase
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
     * Test validación de título - debe ser obligatorio
     */
    public function test_title_is_required(): void
    {
        $taskData = [
            'description' => 'Test Description',
            'status' => 'pending',
            'user_id' => $this->user->id
        ];

        $response = $this->postJson('/api/tasks', $taskData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
        $response->assertJson([
            'errors' => [
                'title' => ['El título es obligatorio.']
            ]
        ]);
    }

    /**
     * Test validación de título - debe tener al menos 5 caracteres
     */
    public function test_title_must_have_minimum_5_characters(): void
    {
        $taskData = [
            'title' => 'Test', // Solo 4 caracteres
            'description' => 'Test Description',
            'status' => 'pending',
            'user_id' => $this->user->id
        ];

        $response = $this->postJson('/api/tasks', $taskData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
        $response->assertJson([
            'errors' => [
                'title' => ['El título debe tener al menos 5 caracteres.']
            ]
        ]);
    }

    /**
     * Test validación de título - no puede superar 255 caracteres
     */
    public function test_title_cannot_exceed_255_characters(): void
    {
        $taskData = [
            'title' => str_repeat('a', 256), // 256 caracteres
            'description' => 'Test Description',
            'status' => 'pending',
            'user_id' => $this->user->id
        ];

        $response = $this->postJson('/api/tasks', $taskData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
        $response->assertJson([
            'errors' => [
                'title' => ['El título no puede superar los 255 caracteres.']
            ]
        ]);
    }

    /**
     * Test validación de descripción - no puede superar 500 caracteres
     */
    public function test_description_cannot_exceed_500_characters(): void
    {
        $taskData = [
            'title' => 'Valid Task Title',
            'description' => str_repeat('a', 501), // 501 caracteres
            'status' => 'pending',
            'user_id' => $this->user->id
        ];

        $response = $this->postJson('/api/tasks', $taskData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['description']);
        $response->assertJson([
            'errors' => [
                'description' => ['La descripción no puede superar los 500 caracteres.']
            ]
        ]);
    }

    /**
     * Test validación de descripción - es opcional
     */
    public function test_description_is_optional(): void
    {
        $taskData = [
            'title' => 'Valid Task Title',
            'status' => 'pending',
            'user_id' => $this->user->id
        ];

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
     * Test validación de status - debe ser obligatorio
     */
    public function test_status_is_required(): void
    {
        $taskData = [
            'title' => 'Valid Task Title',
            'description' => 'Test Description',
            'user_id' => $this->user->id
        ];

        $response = $this->postJson('/api/tasks', $taskData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
        $response->assertJson([
            'errors' => [
                'status' => ['El estado es obligatorio.']
            ]
        ]);
    }

    /**
     * Test validación de status - debe ser uno de los valores permitidos
     */
    public function test_status_must_be_valid_value(): void
    {
        $taskData = [
            'title' => 'Valid Task Title',
            'description' => 'Test Description',
            'status' => 'invalid_status',
            'user_id' => $this->user->id
        ];

        $response = $this->postJson('/api/tasks', $taskData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
        $response->assertJson([
            'errors' => [
                'status' => ['El estado debe ser uno de: pending, in_progress, completed.']
            ]
        ]);
    }

    /**
     * Test validación de status - acepta valores válidos
     */
    public function test_status_accepts_valid_values(): void
    {
        $validStatuses = ['pending', 'in_progress', 'completed'];

        foreach ($validStatuses as $status) {
            $taskData = [
                'title' => 'Valid Task Title',
                'description' => 'Test Description',
                'status' => $status,
                'user_id' => $this->user->id
            ];

            $response = $this->postJson('/api/tasks', $taskData, [
                'Authorization' => 'Bearer ' . $this->validToken
            ]);

            $response->assertStatus(201);
            $response->assertJson([
                'success' => true,
                'message' => 'Task created successfully'
            ]);
        }
    }

    /**
     * Test validación de user_id - debe ser obligatorio
     */
    public function test_user_id_is_required(): void
    {
        $taskData = [
            'title' => 'Valid Task Title',
            'description' => 'Test Description',
            'status' => 'pending'
        ];

        $response = $this->postJson('/api/tasks', $taskData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_id']);
        $response->assertJson([
            'errors' => [
                'user_id' => ['El ID del usuario es obligatorio.']
            ]
        ]);
    }

    /**
     * Test validación de user_id - debe ser un entero
     */
    public function test_user_id_must_be_integer(): void
    {
        $taskData = [
            'title' => 'Valid Task Title',
            'description' => 'Test Description',
            'status' => 'pending',
            'user_id' => 'not_an_integer'
        ];

        $response = $this->postJson('/api/tasks', $taskData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_id']);
        $response->assertJson([
            'errors' => [
                'user_id' => ['El ID del usuario debe ser un número entero.']
            ]
        ]);
    }

    /**
     * Test validación de user_id - debe referirse a un usuario existente
     */
    public function test_user_id_must_exist(): void
    {
        $taskData = [
            'title' => 'Valid Task Title',
            'description' => 'Test Description',
            'status' => 'pending',
            'user_id' => 999 // Usuario que no existe
        ];

        $response = $this->postJson('/api/tasks', $taskData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_id']);
        $response->assertJson([
            'errors' => [
                'user_id' => ['El usuario especificado no existe.']
            ]
        ]);
    }

    /**
     * Test validación de actualización - campos opcionales
     */
    public function test_update_validation_optional_fields(): void
    {
        // Crear una tarea primero
        $task = $this->user->tasks()->create([
            'title' => 'Original Task',
            'description' => 'Original Description',
            'status' => 'pending'
        ]);

        // Actualizar solo el título
        $updateData = [
            'title' => 'Updated Task Title'
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $updateData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Task updated successfully'
        ]);

        // Verificar que solo el título se actualizó
        $task->refresh();
        $this->assertEquals('Updated Task Title', $task->title);
        $this->assertEquals('Original Description', $task->description);
        $this->assertEquals('pending', $task->status);
    }

    /**
     * Test validación exitosa con todos los campos válidos
     */
    public function test_successful_validation_with_all_valid_fields(): void
    {
        $taskData = [
            'title' => 'Valid Task Title',
            'description' => 'Valid task description',
            'status' => 'pending',
            'user_id' => $this->user->id
        ];

        $response = $this->postJson('/api/tasks', $taskData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Task created successfully'
        ]);

        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'title',
                'description',
                'status',
                'user_id',
                'created_at',
                'updated_at',
                'user' => [
                    'id',
                    'name',
                    'email'
                ]
            ],
            'message'
        ]);
    }
}
