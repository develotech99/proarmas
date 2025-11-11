ProArmas - Sistema de Gestión
Sistema de gestión desarrollado en Laravel para el manejo de inventario y control de armas.
📋 Requisitos del Sistema

PHP >= 8.1
Composer
Node.js >= 16.x
MySQL >= 5.7
Git

🚀 Instalación
1. Clonar el repositorio
git clone https://github.com/develotech99/proarmas.git
cd proarmas
2. Instalar dependencias
composer install
npm install
3. Configurar ambiente
cp .env.example .env
php artisan key:generate
4. Configurar base de datos
Edita el archivo .env con tus credenciales de base de datos:
envDB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_base_datos
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
⚠️ IMPORTANTE - No ejecutar migraciones
NO ejecutes php artisan migrate - Este proyecto utiliza una base de datos compartida que ya contiene las tablas necesarias. Solo configura tu conexión en el archivo .env.
5. Crear enlace para storage
php artisan storage:link
6. Compilar assets y ejecutar
npm run dev
php artisan serve
La aplicación estará disponible en: http://localhost:8000
🔧 Comandos Útiles
Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan view:clear
Para desarrollo
npm run dev        # Compilar assets para desarrollo
npm run watch      # Compilar y observar cambios
Para producción
npm run build
php artisan config:cache
🛠️ Tecnologías

Laravel 10.x
MySQL (Base de datos compartida)
HTML5, CSS3, JavaScript
Composer, NPM

🐛 Problemas Comunes
Error de permisos
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
Limpiar dependencias
composer clear-cache
composer install --no-cache
🤝 Contribución

Fork el proyecto
Crea tu rama (git checkout -b feature/nueva-feature)
Commit cambios (git commit -am 'Agregar feature')
Push (git push origin feature/nueva-feature)
Crear Pull Request

📞 Contacto

GitHub: @marino57
Repositorio: ProArmas



<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
