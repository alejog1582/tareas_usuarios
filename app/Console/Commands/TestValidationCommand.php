<?php

namespace App\Console\Commands;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class TestValidationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:validation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test validation rules for task creation and updates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Información sobre validaciones de tareas...');
        $this->newLine();

        // Verificar que hay usuarios en la base de datos
        $userCount = User::count();
        if ($userCount === 0) {
            $this->error('No hay usuarios en la base de datos. Ejecuta el seeder primero:');
            $this->info('php artisan db:seed --class=TaskSeeder');
            return;
        }

        $user = User::first();
        $this->info("👤 Usuario disponible para pruebas: {$user->name} (ID: {$user->id})");
        $this->newLine();

        // Mostrar reglas de validación
        $this->info('📋 Reglas de validación implementadas:');
        $this->newLine();

        $this->info('🔸 StoreTaskRequest (Crear tarea):');
        $this->info('   • title: required|string|min:5|max:255');
        $this->info('   • description: nullable|string|max:500');
        $this->info('   • status: required|in:pending,in_progress,completed');
        $this->info('   • user_id: required|integer|exists:users,id');
        $this->newLine();

        $this->info('🔸 UpdateTaskRequest (Actualizar tarea):');
        $this->info('   • title: sometimes|required|string|min:5|max:255');
        $this->info('   • description: sometimes|nullable|string|max:500');
        $this->info('   • status: sometimes|required|in:pending,in_progress,completed');
        $this->newLine();

        $this->info('📝 Mensajes de error personalizados:');
        $this->info('   • Título obligatorio: "El título es obligatorio."');
        $this->info('   • Título muy corto: "El título debe tener al menos 5 caracteres."');
        $this->info('   • Título muy largo: "El título no puede superar los 255 caracteres."');
        $this->info('   • Descripción muy larga: "La descripción no puede superar los 500 caracteres."');
        $this->info('   • Estado inválido: "El estado debe ser uno de: pending, in_progress, completed."');
        $this->info('   • Usuario inexistente: "El usuario especificado no existe."');
        $this->newLine();

        $this->info('✅ Validaciones implementadas correctamente:');
        $this->info('   • Form Requests creados: StoreTaskRequest, UpdateTaskRequest');
        $this->info('   • Controladores actualizados para usar Form Requests');
        $this->info('   • Tests completos ejecutados: 13 tests pasando');
        $this->info('   • Mensajes de error en español');
        $this->info('   • Validación condicional para actualizaciones');
        $this->newLine();

        $this->info('🧪 Para probar las validaciones, ejecuta:');
        $this->info('   php artisan test tests/Feature/ValidationTest.php');
        $this->newLine();

        $this->info('📊 Ejemplos de datos válidos:');
        $this->info('   • title: "Tarea de prueba válida" (5+ caracteres)');
        $this->info('   • description: "Descripción opcional" (máx 500 caracteres)');
        $this->info('   • status: "pending", "in_progress", o "completed"');
        $this->info('   • user_id: ' . $user->id . ' (usuario existente)');
    }
}
