<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuarios de ejemplo
        $user1 = User::create([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => bcrypt('password123'),
        ]);

        $user2 = User::create([
            'name' => 'María García',
            'email' => 'maria@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Crear tareas para el primer usuario
        $user1->tasks()->createMany([
            [
                'title' => 'Completar documentación del proyecto',
                'description' => 'Escribir la documentación técnica del sistema de tareas',
                'status' => 'in_progress',
            ],
            [
                'title' => 'Revisar código de autenticación',
                'description' => 'Hacer code review del módulo de autenticación',
                'status' => 'pending',
            ],
            [
                'title' => 'Implementar tests unitarios',
                'description' => 'Crear tests para los modelos User y Task',
                'status' => 'completed',
            ],
        ]);

        // Crear tareas para el segundo usuario
        $user2->tasks()->createMany([
            [
                'title' => 'Diseñar interfaz de usuario',
                'description' => 'Crear mockups para la interfaz de gestión de tareas',
                'status' => 'pending',
            ],
            [
                'title' => 'Configurar base de datos',
                'description' => 'Configurar la base de datos de producción',
                'status' => 'in_progress',
            ],
        ]);

        // Probar relaciones - esto es solo para verificar que funcionan
        $this->command->info('Usuarios creados: ' . User::count());
        $this->command->info('Tareas creadas: ' . Task::count());
        $this->command->info('Tareas del usuario 1: ' . $user1->tasks()->count());
        $this->command->info('Tareas pendientes del usuario 1: ' . $user1->pendingTasks()->count());
        $this->command->info('Tareas completadas del usuario 1: ' . $user1->completedTasks()->count());
    }
}
