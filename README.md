# Tareas de Usuarios - API REST

Una aplicación web desarrollada con Laravel para la gestión de tareas de usuarios a través de una API REST.

## 📋 Descripción

Esta aplicación proporciona una API REST completa para la gestión de tareas de usuarios. Incluye funcionalidades como:

- **API REST** con endpoints para gestión de usuarios y tareas
- **Autenticación por token** para operaciones seguras
- **Validación robusta** de datos de entrada
- **Relaciones Eloquent** entre usuarios y tareas
- **Estados de tareas** (pending, in_progress, completed)
- **Pruebas unitarias** completas con PHPUnit

## 🛠️ Tecnologías Utilizadas

- **Laravel 11.x** - Framework PHP
- **PHP 8.2+** - Lenguaje de programación
- **MySQL/SQLite** - Base de datos
- **PHPUnit** - Framework de pruebas
- **Composer** - Gestor de dependencias

## 🚀 Instalación

### Requisitos previos
- PHP 8.2 o superior
- Composer
- MySQL/SQLite
- Git

### Pasos de instalación

1. **Clona el repositorio:**
```bash
git clone https://github.com/alejog1582/tareas_usuarios.git
cd tareas_usuarios
```

2. **Instala las dependencias:**
```bash
composer install
```

3. **Configura el archivo de entorno:**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configura la base de datos en `.env`:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tareas_usuarios
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

# Token para autenticación API
API_TOKEN=tareas_usuarios_api_token_2025_secret_key
```

5. **Ejecuta las migraciones:**
```bash
php artisan migrate
```

6. **Pobla la base de datos con datos de prueba:**
```bash
php artisan db:seed --class=TaskSeeder
```

7. **Inicia el servidor de desarrollo:**
```bash
php artisan serve
```

La aplicación estará disponible en: `http://localhost:8000`

## 🔐 Autenticación

La API utiliza autenticación por token para las operaciones que modifican datos (POST, PUT, DELETE).

### Configuración del token
El token se configura en el archivo `.env`:
```env
API_TOKEN=tareas_usuarios_api_token_2025_secret_key
```

### Uso del token en Postman
Para las rutas que requieren autenticación, incluye el header:
```
Authorization: Bearer tareas_usuarios_api_token_2025_secret_key
```

## 📚 Documentación de la API

### Base URL
```
http://localhost:8000/api
```

### Endpoints disponibles

#### 1. **GET /api/users** - Listar todos los usuarios
- **Método:** GET
- **Autenticación:** No requerida
- **Descripción:** Obtiene la lista de todos los usuarios con el conteo de sus tareas

**Ejemplo de respuesta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Juan Pérez",
            "email": "juan@example.com",
            "created_at": "2025-01-01T12:00:00.000000Z",
            "tasks_count": 3
        }
    ],
    "message": "Users retrieved successfully"
}
```

#### 2. **GET /api/users/{id}/tasks** - Listar tareas de un usuario
- **Método:** GET
- **Autenticación:** No requerida
- **Parámetros:** `id` (ID del usuario)

**Ejemplo de respuesta:**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "Juan Pérez",
            "email": "juan@example.com"
        },
        "tasks": [
            {
                "id": 1,
                "title": "Completar documentación",
                "description": "Revisar y actualizar la documentación del proyecto",
                "status": "in_progress",
                "created_at": "2025-01-01T12:00:00.000000Z",
                "updated_at": "2025-01-01T12:00:00.000000Z"
            }
        ]
    },
    "message": "User tasks retrieved successfully"
}
```

#### 3. **POST /api/tasks** - Crear una nueva tarea
- **Método:** POST
- **Autenticación:** Requerida (Bearer Token)
- **Descripción:** Crea una nueva tarea asignada a un usuario

**Headers requeridos:**
```
Authorization: Bearer tareas_usuarios_api_token_2025_secret_key
Content-Type: application/json
```

**Body JSON:**
```json
{
    "title": "Nueva tarea de prueba",
    "description": "Esta es una descripción de la tarea",
    "status": "pending",
    "user_id": 1
}
```

**Ejemplo de respuesta exitosa (201):**
```json
{
    "success": true,
    "data": {
        "id": 4,
        "title": "Nueva tarea de prueba",
        "description": "Esta es una descripción de la tarea",
        "status": "pending",
        "user_id": 1,
        "created_at": "2025-01-01T12:00:00.000000Z",
        "updated_at": "2025-01-01T12:00:00.000000Z",
        "user": {
            "id": 1,
            "name": "Juan Pérez",
            "email": "juan@example.com"
        }
    },
    "message": "Task created successfully"
}
```

**Ejemplo de respuesta de error (422):**
```json
{
    "message": "El título debe tener al menos 5 caracteres. (and 3 more errors)",
    "errors": {
        "title": ["El título debe tener al menos 5 caracteres."],
        "description": ["La descripción no puede superar los 500 caracteres."],
        "status": ["El estado debe ser uno de: pending, in_progress, completed."],
        "user_id": ["El usuario especificado no existe."]
    }
}
```

#### 4. **PUT /api/tasks/{id}** - Actualizar una tarea
- **Método:** PUT
- **Autenticación:** Requerida (Bearer Token)
- **Parámetros:** `id` (ID de la tarea)
- **Descripción:** Actualiza una tarea existente

**Headers requeridos:**
```
Authorization: Bearer tareas_usuarios_api_token_2025_secret_key
Content-Type: application/json
```

**Body JSON (campos opcionales):**
```json
{
    "title": "Título actualizado",
    "description": "Descripción actualizada",
    "status": "completed"
}
```

**Ejemplo de respuesta exitosa (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Título actualizado",
        "description": "Descripción actualizada",
        "status": "completed",
        "user_id": 1,
        "created_at": "2025-01-01T12:00:00.000000Z",
        "updated_at": "2025-01-01T12:30:00.000000Z",
        "user": {
            "id": 1,
            "name": "Juan Pérez",
            "email": "juan@example.com"
        }
    },
    "message": "Task updated successfully"
}
```

#### 5. **DELETE /api/tasks/{id}** - Eliminar una tarea
- **Método:** DELETE
- **Autenticación:** Requerida (Bearer Token)
- **Parámetros:** `id` (ID de la tarea)
- **Descripción:** Elimina una tarea específica

**Headers requeridos:**
```
Authorization: Bearer tareas_usuarios_api_token_2025_secret_key
```

**Ejemplo de respuesta exitosa (200):**
```json
{
    "success": true,
    "message": "Task deleted successfully"
}
```

**Ejemplo de respuesta de error (404):**
```json
{
    "success": false,
    "message": "Task not found"
}
```

## 📝 Validaciones

### Campos de tarea
- **title**: Obligatorio, mínimo 5 caracteres, máximo 255 caracteres
- **description**: Opcional, máximo 500 caracteres
- **status**: Obligatorio, valores permitidos: `pending`, `in_progress`, `completed`
- **user_id**: Obligatorio, debe ser un ID de usuario existente

### Códigos de respuesta HTTP
- **200**: OK - Operación exitosa
- **201**: Created - Recurso creado exitosamente
- **401**: Unauthorized - Token de autenticación inválido o faltante
- **404**: Not Found - Recurso no encontrado
- **422**: Unprocessable Entity - Error de validación
- **500**: Internal Server Error - Error interno del servidor

## 🧪 Pruebas

### Ejecutar todas las pruebas
```bash
php artisan test
```

### Ejecutar pruebas específicas
```bash
# Pruebas unitarias
php artisan test tests/Feature/UnitTest.php

# Pruebas de validación
php artisan test tests/Feature/ValidationTest.php

# Pruebas de middleware
php artisan test tests/Feature/MiddlewareTest.php
```

### Comandos de prueba disponibles
```bash
# Probar relaciones Eloquent
php artisan test:relations

# Probar endpoints de la API
php artisan test:api-endpoints

# Probar middleware de autenticación
php artisan test:middleware

# Probar validaciones
php artisan test:validation

# Información sobre pruebas unitarias
php artisan test:unit-tests
```

## 📁 Estructura del Proyecto

```
tareas_usuarios/
├── app/
│   ├── Console/Commands/          # Comandos Artisan personalizados
│   ├── Http/
│   │   ├── Controllers/Api/       # Controladores de la API
│   │   ├── Middleware/            # Middleware personalizado
│   │   └── Requests/              # Form Requests para validación
│   └── Models/                    # Modelos Eloquent
├── database/
│   ├── migrations/                # Migraciones de base de datos
│   └── seeders/                   # Seeders para datos de prueba
├── routes/
│   └── api.php                    # Rutas de la API
├── tests/
│   └── Feature/                   # Pruebas de funcionalidad
└── README.md                      # Este archivo
```

## 🔧 Comandos útiles

```bash
# Ver todas las rutas disponibles
php artisan route:list --path=api

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Verificar configuración
php artisan config:show app.api_token
```

## 📊 Estadísticas del proyecto

- **Total de tests**: 25 tests pasando
- **Total de assertions**: 137 assertions
- **Cobertura**: Creación, lectura, actualización, eliminación, validación, autenticación
- **Endpoints**: 5 endpoints REST
- **Modelos**: 2 modelos con relaciones Eloquent
- **Middleware**: 1 middleware de autenticación personalizado

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 👨‍💻 Autor

**Alejo García**
- GitHub: [@alejog1582](https://github.com/alejog1582)
- Proyecto: [tareas_usuarios](https://github.com/alejog1582/tareas_usuarios)
