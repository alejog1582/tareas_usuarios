<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserCreationTest extends TestCase
{
    use RefreshDatabase;

    private $validToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar el token válido
        $this->validToken = config('app.api_token');
    }

    /**
     * Test: Crear usuario exitosamente con datos válidos
     */
    public function test_can_create_user_successfully(): void
    {
        $userData = [
            'name' => 'Nuevo Usuario',
            'email' => 'nuevo@example.com'
        ];

        $response = $this->postJson('/api/users', $userData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'User created successfully'
        ]);

        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'name',
                'email',
                'created_at',
                'updated_at'
            ],
            'message'
        ]);

        $response->assertJson([
            'data' => [
                'name' => 'Nuevo Usuario',
                'email' => 'nuevo@example.com'
            ]
        ]);

        // Verificar que el usuario se guardó en la base de datos
        $this->assertDatabaseHas('users', [
            'name' => 'Nuevo Usuario',
            'email' => 'nuevo@example.com'
        ]);

        // Verificar que el password se estableció correctamente
        $user = User::where('email', 'nuevo@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(password_verify('123456', $user->password));
    }

    /**
     * Test: No se puede crear usuario sin token de autenticación
     */
    public function test_cannot_create_user_without_token(): void
    {
        $userData = [
            'name' => 'Usuario Sin Token',
            'email' => 'sin_token@example.com'
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Authorization header is required'
        ]);
    }

    /**
     * Test: No se puede crear usuario con token inválido
     */
    public function test_cannot_create_user_with_invalid_token(): void
    {
        $userData = [
            'name' => 'Usuario Token Inválido',
            'email' => 'token_invalido@example.com'
        ];

        $response = $this->postJson('/api/users', $userData, [
            'Authorization' => 'Bearer token_invalido'
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid API token'
        ]);
    }

    /**
     * Test: No se puede crear usuario con email duplicado
     */
    public function test_cannot_create_user_with_duplicate_email(): void
    {
        // Crear un usuario existente
        User::factory()->create([
            'name' => 'Usuario Existente',
            'email' => 'existente@example.com'
        ]);

        $userData = [
            'name' => 'Nuevo Usuario',
            'email' => 'existente@example.com' // Email duplicado
        ];

        $response = $this->postJson('/api/users', $userData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $response->assertJson([
            'errors' => [
                'email' => ['El email ya está registrado.']
            ]
        ]);
    }

    /**
     * Test: No se puede crear usuario sin nombre
     */
    public function test_cannot_create_user_without_name(): void
    {
        $userData = [
            'email' => 'sin_nombre@example.com'
        ];

        $response = $this->postJson('/api/users', $userData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
        $response->assertJson([
            'errors' => [
                'name' => ['El nombre es obligatorio.']
            ]
        ]);
    }

    /**
     * Test: No se puede crear usuario sin email
     */
    public function test_cannot_create_user_without_email(): void
    {
        $userData = [
            'name' => 'Usuario Sin Email'
        ];

        $response = $this->postJson('/api/users', $userData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $response->assertJson([
            'errors' => [
                'email' => ['El email es obligatorio.']
            ]
        ]);
    }

    /**
     * Test: No se puede crear usuario con email inválido
     */
    public function test_cannot_create_user_with_invalid_email(): void
    {
        $userData = [
            'name' => 'Usuario Email Inválido',
            'email' => 'email_invalido'
        ];

        $response = $this->postJson('/api/users', $userData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $response->assertJson([
            'errors' => [
                'email' => ['El email debe tener un formato válido.']
            ]
        ]);
    }

    /**
     * Test: No se puede crear usuario con nombre muy corto
     */
    public function test_cannot_create_user_with_short_name(): void
    {
        $userData = [
            'name' => 'A', // Solo 1 carácter
            'email' => 'nombre_corto@example.com'
        ];

        $response = $this->postJson('/api/users', $userData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
        $response->assertJson([
            'errors' => [
                'name' => ['El nombre debe tener al menos 2 caracteres.']
            ]
        ]);
    }

    /**
     * Test: Verificar que el password por defecto es 123456
     */
    public function test_user_created_with_default_password(): void
    {
        $userData = [
            'name' => 'Usuario Password Test',
            'email' => 'password_test@example.com'
        ];

        $response = $this->postJson('/api/users', $userData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        $response->assertStatus(201);

        // Verificar que el usuario se creó con el password por defecto
        $user = User::where('email', 'password_test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(password_verify('123456', $user->password));
        $this->assertFalse(password_verify('otro_password', $user->password));
    }

    /**
     * Test: Verificar que el usuario creado no incluye el password en la respuesta
     */
    public function test_user_response_does_not_include_password(): void
    {
        $userData = [
            'name' => 'Usuario Sin Password',
            'email' => 'sin_password@example.com'
        ];

        $response = $this->postJson('/api/users', $userData, [
            'Authorization' => 'Bearer ' . $this->validToken
        ]);

        $response->assertStatus(201);
        
        $responseData = $response->json('data');
        $this->assertArrayNotHasKey('password', $responseData);
        $this->assertArrayNotHasKey('password_hash', $responseData);
    }
}
