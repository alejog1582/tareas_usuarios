<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Task;
use Illuminate\Console\Command;

class TestRelationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:relations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Eloquent relationships between User and Task models';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Probando relaciones Eloquent...');
        $this->newLine();

        // Obtener el primer usuario
        $user = User::first();
        if (!$user) {
            $this->error('No hay usuarios en la base de datos. Ejecuta el seeder primero.');
            return;
        }

        $this->info("👤 Usuario: {$user->name}");
        $this->info("📧 Email: {$user->email}");
        $this->newLine();

        // Probar relaciones User -> Tasks
        $this->info('📋 Relaciones User -> Tasks:');
        $this->info("   • Total tareas: {$user->tasks()->count()}");
        $this->info("   • Tareas pendientes: {$user->pendingTasks()->count()}");
        $this->info("   • Tareas en progreso: {$user->inProgressTasks()->count()}");
        $this->info("   • Tareas completadas: {$user->completedTasks()->count()}");
        $this->newLine();

        // Obtener la primera tarea
        $task = Task::first();
        if (!$task) {
            $this->error('No hay tareas en la base de datos.');
            return;
        }

        // Probar relaciones Task -> User
        $this->info('🔗 Relaciones Task -> User:');
        $this->info("   • Tarea: {$task->title}");
        $this->info("   • Propietario: {$task->user->name}");
        $this->info("   • Estado: {$task->status}");
        $this->newLine();

        // Probar métodos de estado
        $this->info('✅ Métodos de estado:');
        $this->info("   • isPending(): " . ($task->isPending() ? 'true' : 'false'));
        $this->info("   • isInProgress(): " . ($task->isInProgress() ? 'true' : 'false'));
        $this->info("   • isCompleted(): " . ($task->isCompleted() ? 'true' : 'false'));
        $this->newLine();

        // Probar scopes
        $this->info('🔍 Probando scopes:');
        $this->info("   • Task::pending()->count(): " . Task::pending()->count());
        $this->info("   • Task::inProgress()->count(): " . Task::inProgress()->count());
        $this->info("   • Task::completed()->count(): " . Task::completed()->count());
        $this->info("   • Task::forUser({$user->id})->count(): " . Task::forUser($user->id)->count());
        $this->newLine();

        $this->info('✅ ¡Todas las relaciones funcionan correctamente!');
    }
}
