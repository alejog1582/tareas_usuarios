# Tareas de Usuarios

Una aplicación web desarrollada con Laravel para la gestión de tareas de usuarios.

## Descripción

Esta aplicación permite a los usuarios registrarse, iniciar sesión y gestionar sus tareas personales. Incluye funcionalidades como:

- Sistema de autenticación de usuarios
- CRUD completo de tareas
- Asignación de tareas a usuarios específicos
- Estados de tareas (pendiente, en progreso, completada)
- Interfaz web moderna y responsiva

## Tecnologías Utilizadas

- **Laravel 12.x** - Framework PHP
- **PHP 8.2+** - Lenguaje de programación
- **MySQL/SQLite** - Base de datos
- **Bootstrap** - Framework CSS
- **JavaScript** - Interactividad del frontend

## Instalación

1. Clona el repositorio:
```bash
git clone https://github.com/alejog1582/tareas_usuarios.git
cd tareas_usuarios
```

2. Instala las dependencias:
```bash
composer install
```

3. Configura el archivo `.env`:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configura la base de datos en el archivo `.env`

5. Ejecuta las migraciones:
```bash
php artisan migrate
```

6. Inicia el servidor de desarrollo:
```bash
php artisan serve
```

## Estructura del Proyecto

- `app/Models/` - Modelos de la aplicación (User, Task)
- `app/Http/Controllers/` - Controladores
- `database/migrations/` - Migraciones de base de datos
- `resources/views/` - Vistas Blade
- `routes/` - Definición de rutas

## Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.
