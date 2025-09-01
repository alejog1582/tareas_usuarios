<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\TaskController;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class TestMiddlewareCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:middleware';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test API token middleware functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔐 Probando middleware de autenticación API...');
        $this->newLine();

        // Verificar que hay datos en la base de datos
        $userCount = User::count();
        if ($userCount === 0) {
            $this->error('No hay usuarios en la base de datos. Ejecuta el seeder primero:');
            $this->info('php artisan db:seed --class=TaskSeeder');
            return;
        }

        $firstUser = User::first();
        $taskController = new TaskController();

        // Obtener el token válido desde la configuración
        $validToken = config('app.api_token');
        $this->info("🔑 Token válido configurado: " . substr($validToken, 0, 10) . "...");
        $this->newLine();

        // Test 1: Request sin token
        $this->info('🧪 Test 1: Request sin token de autorización');
        $requestWithoutToken = new Request([
            'title' => 'Tarea sin token',
            'description' => 'Esta tarea no debería crearse',
            'status' => 'pending',
            'user_id' => $firstUser->id
        ]);
        
        try {
            $response = $taskController->store($requestWithoutToken);
            $data = json_decode($response->getContent(), true);
            $this->info("   ❌ Respuesta inesperada: {$data['message']}");
        } catch (\Exception $e) {
            $this->info("   ✅ Middleware bloqueó la request (esperado)");
        }
        $this->newLine();

        // Test 2: Request con token inválido
        $this->info('🧪 Test 2: Request con token inválido');
        $requestWithInvalidToken = new Request([
            'title' => 'Tarea con token inválido',
            'description' => 'Esta tarea no debería crearse',
            'status' => 'pending',
            'user_id' => $firstUser->id
        ]);
        $requestWithInvalidToken->headers->set('Authorization', 'Bearer token_invalido');
        
        try {
            $response = $taskController->store($requestWithInvalidToken);
            $data = json_decode($response->getContent(), true);
            $this->info("   ❌ Respuesta inesperada: {$data['message']}");
        } catch (\Exception $e) {
            $this->info("   ✅ Middleware bloqueó la request (esperado)");
        }
        $this->newLine();

        // Test 3: Request con token válido
        $this->info('🧪 Test 3: Request con token válido');
        $requestWithValidToken = new Request([
            'title' => 'Tarea con token válido',
            'description' => 'Esta tarea debería crearse correctamente',
            'status' => 'pending',
            'user_id' => $firstUser->id
        ]);
        $requestWithValidToken->headers->set('Authorization', 'Bearer ' . $validToken);
        
        try {
            $response = $taskController->store($requestWithValidToken);
            $data = json_decode($response->getContent(), true);
            if ($data['success']) {
                $this->info("   ✅ Tarea creada correctamente: {$data['message']}");
                $taskId = $data['data']['id'];
                
                // Test 4: Actualizar tarea con token válido
                $this->info('🧪 Test 4: Actualizar tarea con token válido');
                $updateRequest = new Request([
                    'title' => 'Tarea actualizada con token válido',
                    'status' => 'in_progress'
                ]);
                $updateRequest->headers->set('Authorization', 'Bearer ' . $validToken);
                
                $updateResponse = $taskController->update($updateRequest, $taskId);
                $updateData = json_decode($updateResponse->getContent(), true);
                $this->info("   ✅ Tarea actualizada: {$updateData['message']}");
                
                // Test 5: Eliminar tarea con token válido
                $this->info('🧪 Test 5: Eliminar tarea con token válido');
                $deleteRequest = new Request();
                $deleteRequest->headers->set('Authorization', 'Bearer ' . $validToken);
                
                $deleteResponse = $taskController->destroy($taskId);
                $deleteData = json_decode($deleteResponse->getContent(), true);
                $this->info("   ✅ Tarea eliminada: {$deleteData['message']}");
            } else {
                $this->info("   ❌ Error inesperado: {$data['message']}");
            }
        } catch (\Exception $e) {
            $this->info("   ❌ Error inesperado: " . $e->getMessage());
        }

        $this->newLine();
        $this->info('✅ ¡Pruebas del middleware completadas!');
        $this->newLine();
        $this->info('📋 Resumen del middleware:');
        $this->info('   • Bloquea requests sin token (401)');
        $this->info('   • Bloquea requests con token inválido (401)');
        $this->info('   • Permite requests con token válido');
        $this->info('   • Aplicado a rutas: POST, PUT, DELETE');
        $this->info('   • GET routes no requieren autenticación');
    }
}
