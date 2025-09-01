<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index(): JsonResponse
    {
        $users = User::select('id', 'name', 'email', 'created_at')
            ->withCount('tasks')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users,
            'message' => 'Users retrieved successfully'
        ]);
    }

    /**
     * Get all tasks for a specific user.
     */
    public function tasks(string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $tasks = $user->tasks()
            ->select('id', 'title', 'description', 'status', 'created_at', 'updated_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ],
                'tasks' => $tasks
            ],
            'message' => 'User tasks retrieved successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Not implemented for this API
        return response()->json([
            'success' => false,
            'message' => 'Method not implemented'
        ], 405);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Not implemented for this API
        return response()->json([
            'success' => false,
            'message' => 'Method not implemented'
        ], 405);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Not implemented for this API
        return response()->json([
            'success' => false,
            'message' => 'Method not implemented'
        ], 405);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Not implemented for this API
        return response()->json([
            'success' => false,
            'message' => 'Method not implemented'
        ], 405);
    }
}
