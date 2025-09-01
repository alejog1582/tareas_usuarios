<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UnitTest extends TestCase
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
     * Test 1: Que un usuario puede crear una tarea correctamente
     * 
     * Este test verifica que:
     * - Un usuario puede crear una tarea con datos válidos
     * - La tarea se guarda correctamente en la base de datos
     * - Se retorna la respuesta correcta con código 201
     * - La tarea incluye la relación con el usuario
     */
    public function test_user_can_create_task_successfully(): void
    {
        // Datos válidos para crear una tarea
        $taskData = [
            'title' => 'Tarea de prueba unitaria',
            'description' => 'Esta es una descripción válida para la tarea de prueba',
            'status' => 'pending',
            'user_id' => $this->user->id
        ];

        // Hacer la petición POST para crear la tarea
        $response = $this->postJson('/api/tasks', $taskData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(201);
        
        // Verificar la estructura de la respuesta JSON
        $response->assertJson([
            'success' => true,
            'message' => 'Task created successfully'
        ]);

        // Verificar que la respuesta incluye los datos de la tarea
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

        // Verificar que los datos de la tarea son correctos
        $response->assertJson([
            'data' => [
                'title' => 'Tarea de prueba unitaria',
                'description' => 'Esta es una descripción válida para la tarea de prueba',
                'status' => 'pending',
                'user_id' => $this->user->id,
                'user' => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email
                ]
            ]
        ]);

        // Verificar que la tarea se guardó en la base de datos
        $this->assertDatabaseHas('tasks', [
            'title' => 'Tarea de prueba unitaria',
            'description' => 'Esta es una descripción válida para la tarea de prueba',
            'status' => 'pending',
            'user_id' => $this->user->id
        ]);

        // Verificar que la tarea tiene la relación correcta con el usuario
        $task = Task::where('title', 'Tarea de prueba unitaria')->first();
        $this->assertNotNull($task);
        $this->assertEquals($this->user->id, $task->user_id);
        $this->assertEquals($this->user->name, $task->user->name);
        $this->assertEquals($this->user->email, $task->user->email);
    }

    /**
     * Test 2: Que no se puede crear una tarea con datos inválidos
     * 
     * Este test verifica que:
     * - No se puede crear una tarea con datos inválidos
     * - Se retorna el código de error 422 (Unprocessable Entity)
     * - Se incluyen los errores de validación en la respuesta
     * - La tarea no se guarda en la base de datos
     */
    public function test_cannot_create_task_with_invalid_data(): void
    {
        // Datos inválidos para crear una tarea
        $invalidTaskData = [
            'title' => 'Test', // Muy corto (menos de 5 caracteres)
            'description' => str_repeat('a', 501), // Muy largo (más de 500 caracteres)
            'status' => 'invalid_status', // Estado inválido
            'user_id' => 999 // Usuario que no existe
        ];

        // Hacer la petición POST para crear la tarea
        $response = $this->postJson('/api/tasks', $invalidTaskData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        // Verificar que la respuesta es un error de validación
        $response->assertStatus(422);
        
        // Verificar que se incluyen los errores de validación
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'title',
                'description',
                'status',
                'user_id'
            ]
        ]);

        // Verificar que los mensajes de error son correctos
        $response->assertJson([
            'errors' => [
                'title' => ['El título debe tener al menos 5 caracteres.'],
                'description' => ['La descripción no puede superar los 500 caracteres.'],
                'status' => ['El estado debe ser uno de: pending, in_progress, completed.'],
                'user_id' => ['El usuario especificado no existe.']
            ]
        ]);

        // Verificar que la tarea NO se guardó en la base de datos
        $this->assertDatabaseMissing('tasks', [
            'title' => 'Test',
            'user_id' => 999
        ]);

        // Verificar que no se crearon tareas con datos inválidos
        $this->assertEquals(0, Task::where('user_id', 999)->count());
    }

    /**
     * Test 3: Que una tarea puede ser eliminada correctamente
     * 
     * Este test verifica que:
     * - Una tarea existente puede ser eliminada
     * - Se retorna la respuesta correcta con código 200
     * - La tarea se elimina de la base de datos
     * - No se puede acceder a la tarea después de eliminarla
     */
    public function test_task_can_be_deleted_successfully(): void
    {
        // Crear una tarea primero
        $task = Task::create([
            'title' => 'Tarea para eliminar',
            'description' => 'Esta tarea será eliminada en el test',
            'status' => 'pending',
            'user_id' => $this->user->id
        ]);

        // Verificar que la tarea existe en la base de datos
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Tarea para eliminar',
            'user_id' => $this->user->id
        ]);

        // Hacer la petición DELETE para eliminar la tarea
        $response = $this->deleteJson("/api/tasks/{$task->id}", [], [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);
        
        // Verificar la estructura de la respuesta JSON
        $response->assertJson([
            'success' => true,
            'message' => 'Task deleted successfully'
        ]);

        // Verificar que la respuesta tiene la estructura correcta
        $response->assertJsonStructure([
            'success',
            'message'
        ]);

        // Verificar que la tarea se eliminó de la base de datos
        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
            'title' => 'Tarea para eliminar'
        ]);

        // Verificar que no se puede encontrar la tarea
        $deletedTask = Task::find($task->id);
        $this->assertNull($deletedTask);

        // Verificar que el contador de tareas del usuario disminuyó
        $this->assertEquals(0, $this->user->tasks()->count());
    }

    /**
     * Test adicional: Verificar que no se puede eliminar una tarea inexistente
     */
    public function test_cannot_delete_nonexistent_task(): void
    {
        // Intentar eliminar una tarea que no existe
        $response = $this->deleteJson('/api/tasks/999', [], [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        // Verificar que la respuesta es un error 404
        $response->assertStatus(404);
        
        // Verificar la estructura de la respuesta de error
        $response->assertJson([
            'success' => false,
            'message' => 'Task not found'
        ]);
    }

    /**
     * Test adicional: Verificar que se puede crear una tarea sin descripción
     */
    public function test_can_create_task_without_description(): void
    {
        // Datos válidos sin descripción
        $taskData = [
            'title' => 'Tarea sin descripción',
            'status' => 'in_progress',
            'user_id' => $this->user->id
        ];

        // Hacer la petición POST para crear la tarea
        $response = $this->postJson('/api/tasks', $taskData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        // Verificar que la respuesta es exitosa
        $response->assertStatus(201);
        
        // Verificar que la tarea se guardó correctamente
        $this->assertDatabaseHas('tasks', [
            'title' => 'Tarea sin descripción',
            'description' => null,
            'status' => 'in_progress',
            'user_id' => $this->user->id
        ]);
    }
}
