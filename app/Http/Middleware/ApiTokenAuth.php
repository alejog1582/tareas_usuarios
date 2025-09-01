<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Obtener el token del encabezado Authorization
        $authHeader = $request->header('Authorization');
        
        // Verificar que el encabezado Authorization esté presente
        if (!$authHeader) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization header is required'
            ], 401);
        }

        // Extraer el token del formato "Bearer {token}" o solo el token
        $token = null;
        if (str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
        } else {
            $token = $authHeader;
        }

        // Verificar que el token no esté vacío
        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'API token is required'
            ], 401);
        }

        // Obtener el token válido desde la configuración
        $validToken = config('app.api_token');
        
        // Verificar que el token válido esté configurado
        if (empty($validToken)) {
            return response()->json([
                'success' => false,
                'message' => 'API token not configured on server'
            ], 500);
        }

        // Verificar que el token proporcionado coincida con el token válido
        if (!hash_equals($validToken, $token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API token'
            ], 401);
        }

        // Si todo está correcto, continuar con la solicitud
        return $next($request);
    }
}
