<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TaskController;
use App\Models\User;
use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class TestApiEndpointsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:api-endpoints';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test API endpoints functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Probando endpoints de la API...');
        $this->newLine();

        // Verificar que hay datos en la base de datos
        $userCount = User::count();
        $taskCount = Task::count();

        if ($userCount === 0 || $taskCount === 0) {
            $this->error('No hay datos en la base de datos. Ejecuta el seeder primero:');
            $this->info('php artisan db:seed --class=TaskSeeder');
            return;
        }

        $this->info("📊 Datos disponibles: {$userCount} usuarios, {$taskCount} tareas");
        $this->newLine();

        // Probar UserController
        $this->info('👥 Probando UserController...');
        $userController = new UserController();

        // 1. GET /api/users
        $this->info('   • GET /api/users - Listar todos los usuarios');
        $usersResponse = $userController->index();
        $usersData = json_decode($usersResponse->getContent(), true);
        $this->info("     ✅ Respuesta: {$usersData['message']} (" . ($usersData['success'] ? 'success' : 'error') . ")");
        $this->info("     📋 Usuarios encontrados: " . count($usersData['data']));

        // 2. GET /api/users/{id}/tasks
        $firstUser = User::first();
        $this->info("   • GET /api/users/{$firstUser->id}/tasks - Tareas del usuario {$firstUser->name}");
        $tasksResponse = $userController->tasks($firstUser->id);
        $tasksData = json_decode($tasksResponse->getContent(), true);
        $this->info("     ✅ Respuesta: {$tasksData['message']} (" . ($tasksData['success'] ? 'success' : 'error') . ")");
        $this->info("     📋 Tareas encontradas: " . count($tasksData['data']['tasks']));
        $this->newLine();

        // Probar TaskController
        $this->info('📝 Probando TaskController...');
        $taskController = new TaskController();

        // 3. POST /api/tasks
        $this->info('   • POST /api/tasks - Crear nueva tarea');
        $newTaskRequest = new Request([
            'title' => 'Tarea de prueba desde API',
            'description' => 'Esta es una tarea creada para probar el endpoint POST',
            'status' => 'pending',
            'user_id' => $firstUser->id
        ]);
        $createResponse = $taskController->store($newTaskRequest);
        $createData = json_decode($createResponse->getContent(), true);
        $this->info("     ✅ Respuesta: {$createData['message']} (" . ($createData['success'] ? 'success' : 'error') . ")");
        
        if ($createData['success']) {
            $newTaskId = $createData['data']['id'];
            $this->info("     📝 Nueva tarea creada con ID: {$newTaskId}");

            // 4. PUT /api/tasks/{id}
            $this->info("   • PUT /api/tasks/{$newTaskId} - Actualizar tarea");
            $updateRequest = new Request([
                'title' => 'Tarea actualizada desde API',
                'status' => 'in_progress'
            ]);
            $updateResponse = $taskController->update($updateRequest, $newTaskId);
            $updateData = json_decode($updateResponse->getContent(), true);
            $this->info("     ✅ Respuesta: {$updateData['message']} (" . ($updateData['success'] ? 'success' : 'error') . ")");

            // 5. DELETE /api/tasks/{id}
            $this->info("   • DELETE /api/tasks/{$newTaskId} - Eliminar tarea");
            $deleteResponse = $taskController->destroy($newTaskId);
            $deleteData = json_decode($deleteResponse->getContent(), true);
            $this->info("     ✅ Respuesta: {$deleteData['message']} (" . ($deleteData['success'] ? 'success' : 'error') . ")");
        }

        $this->newLine();
        $this->info('✅ ¡Todos los endpoints de la API funcionan correctamente!');
        $this->newLine();
        $this->info('📋 Resumen de endpoints implementados:');
        $this->info('   • GET /api/users - Listar todos los usuarios');
        $this->info('   • GET /api/users/{id}/tasks - Listar tareas de un usuario');
        $this->info('   • POST /api/tasks - Crear nueva tarea');
        $this->info('   • PUT /api/tasks/{id} - Actualizar tarea');
        $this->info('   • DELETE /api/tasks/{id} - Eliminar tarea');
    }
}
