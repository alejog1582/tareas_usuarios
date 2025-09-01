<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TaskController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API Routes for Users
Route::prefix('api')->group(function () {
    // GET /api/users - Listar todos los usuarios
    Route::get('/users', [UserController::class, 'index']);
    
    // GET /api/users/{id}/tasks - Listar todas las tareas de un usuario específico
    Route::get('/users/{id}/tasks', [UserController::class, 'tasks']);
    
    // POST /api/tasks - Crear una nueva tarea
    Route::post('/tasks', [TaskController::class, 'store']);
    
    // PUT /api/tasks/{id} - Actualizar el título, descripción o estado de una tarea
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    
    // DELETE /api/tasks/{id} - Eliminar una tarea específica
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
});
