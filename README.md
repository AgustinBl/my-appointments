# My Appointments

Aplicación web para gestión de turnos médicos, desarrollada con Laravel 6.x.

## Requisitos previos

- Docker Desktop (Windows/Mac) o Docker Engine + Docker Compose (Linux)
- Git

## Despliegue local con Docker Compose

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio>
cd my-appointments
```

### 2. Configurar variables de entorno

```bash
cp .env.docker .env
```

### 3. Levantar los servicios

```bash
docker compose up --build
```

Esto levanta automáticamente:
- **Nginx** en `http://localhost`
- **PHP-FPM 7.4** con extensiones necesarias (pdo_mysql, mbstring, redis, gd, etc.)
- **MySQL 5.7** en `localhost:3306`
- **Redis** en `localhost:6379`
- **Node.js** para compilar assets con Laravel Mix

En el primer arranque, el contenedor `app` ejecuta automáticamente:
1. `composer install`
2. `php artisan key:generate --force`
3. `php artisan migrate --force --seed`

El contenedor `node` compila los assets por separado (`npm install && npm run dev`).

### 4. Acceder a la aplicación

- App: [http://localhost](http://localhost)
- Base de datos: `mysql://laravel:secret@localhost:3306/laravel`
- Redis: `localhost:6379`

## Usuarios de prueba (seeders)

| Nombre | Email | Rol | Password |
|--------|-------|-----|----------|
| Admin Test | test@gmail.com | admin | test123 |
| Paciente 1 | paciente1@gmail.com | patient | test123 |
| Medico 1 | medico1@gmail.com | doctor | test123 |

## Comandos útiles

```bash
# Levantar servicios en segundo plano
docker compose up -d

# Ver logs
docker compose logs -f

# Ejecutar comandos Artisan dentro del contenedor PHP
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed

# Detener servicios
docker compose down

# Detener servicios y borrar volúmenes (resetea la BD)
docker compose down -v

# Reconstruir imágenes
docker compose build --no-cache

# Recompilar assets (solo si editaste resources/js o resources/sass)
docker compose run --rm node
```

> El servicio `node` no arranca con `docker compose up`: si el build de
> webpack falla, igual sobrescribe los assets ya compilados de `public/`.
> Se ejecuta a propósito con el comando de arriba. Si un build sale mal,
> `git checkout -- public/` restaura los assets que funcionaban.

## Estructura de servicios

| Servicio | Imagen | Puerto | Función |
|----------|--------|--------|---------|
| `nginx` | `nginx:alpine` | `80` | Servidor web |
| `app` | `php:7.4-fpm` | `9000` | PHP-FPM con Composer |
| `mysql` | `mysql:5.7` | `3306` | Base de datos |
| `redis` | `redis:alpine` | `6379` | Cache y sesiones |
| `node` | `node:16-alpine` | - | Compilación de assets (bajo demanda, no arranca solo) |

## Rendimiento y recarga de código

**Después de editar código PHP hay que recargar php-fpm** para que el cambio
se vea:

```bash
docker compose kill -s SIGUSR2 app
```

El motivo es que OPcache está configurado con `validate_timestamps=0`. Sobre
el bind mount de Windows, dejar que PHP verifique en cada request si los
~1000 archivos de Laravel cambiaron cuesta el 95% del tiempo de respuesta:

| Configuración | `/login` | `/home` |
|---------------|----------|---------|
| Sin OPcache (original) | 1.95s | 2.10s |
| OPcache validando timestamps | 1.42s | 1.62s |
| **OPcache sin validar (actual)** | **0.08s** | **0.14s** |

Los cambios en Blade, JS, CSS y `.env` **no** necesitan la recarga: solo el
código PHP (controladores, modelos, rutas, config).

Si preferís que los cambios se detecten solos y no te importa la lentitud,
poné `opcache.validate_timestamps=1` en `php/opcache.ini` y reconstruí con
`docker compose up -d --build app`.

## Notas

- Los archivos de código se montan como bind mount, por lo que los cambios locales se reflejan sin reconstruir.
- La base de datos persiste en el volumen `mysql_data`.
- Para producción, usar `APP_DEBUG=false` y configurar un `APP_KEY` seguro.

## Licencia

MIT License.
