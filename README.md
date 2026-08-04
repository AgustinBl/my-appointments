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
| Agustin Blanco | agustin.e.blanco@gmail.com | admin | agux1994 |
| Paciente 1 | paciente1@gmail.com | patient | agux1994 |
| Medico 1 | medico1@gmail.com | doctor | agux1994 |

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

## Notas

- Los archivos de código se montan como bind mount, por lo que los cambios locales se reflejan sin reconstruir.
- La base de datos persiste en el volumen `mysql_data`.
- Para producción, usar `APP_DEBUG=false` y configurar un `APP_KEY` seguro.

## Licencia

MIT License.
