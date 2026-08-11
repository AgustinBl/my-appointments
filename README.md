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

## Notificaciones push (Firebase Cloud Messaging)

La app usa la **API HTTP v1** de FCM. La API legacy (`fcm.googleapis.com/fcm/send`
con `FCM_SERVER_KEY`) fue dada de baja por Google en junio de 2024, así que ese
server key ya no sirve: ahora hay que usar la *service account* del proyecto.

### Configuración

1. En [Firebase Console](https://console.firebase.google.com) → **Configuración
   del proyecto** → **Cuentas de servicio** → **Generar nueva clave privada**.
   Se descarga un JSON.
2. Guardarlo como `storage/app/firebase/service-account.json` (esa carpeta está
   en `.gitignore`: la clave privada no va al repo).
3. Recargar php-fpm: `docker compose kill -s SIGUSR2 app`.

Si preferís otra ubicación, o pasar el JSON entero por variable de entorno
(útil en producción):

```env
FCM_CREDENTIALS=/ruta/al/service-account.json
# o bien
FCM_CREDENTIALS='{"type":"service_account","project_id":"...","private_key":"..."}'
```

`FCM_PROJECT_ID` es opcional: si no está, se toma el `project_id` del JSON.

### Cómo funciona

- `App\Services\Fcm` firma un JWT con la clave privada, lo canjea por un access
  token OAuth2 (cacheado ~1 hora) y postea a
  `https://fcm.googleapis.com/v1/projects/{project}/messages:send`.
- La v1 no soporta multicast: el envío masivo desde `/home` (form "Notificación
  general") manda un request por dispositivo.
- Los tokens que Firebase reporta como `UNREGISTERED`/`NOT_FOUND` (app
  desinstalada o token rotado) se borran solos de `users.device_token`.
- Ningún error de FCM corta el flujo: si falla el envío, la cita se cancela o
  confirma igual y el problema queda en `storage/logs/laravel.log`.

Sin credenciales configuradas la app no rompe: el form de notificación general
avisa que falta `FCM_CREDENTIALS` y los envíos individuales devuelven `false`.

## Autenticación de la API (JWT)

Las rutas de `routes/api.php` detrás de `auth:api` se autentican con un JWT en
el header `Authorization: Bearer <token>`. La implementación es propia y vive en
`app/Auth/`:

| Clase | Responsabilidad |
| --- | --- |
| `JwtCodec` | Arma y verifica el token: base64url + firma HMAC-SHA512. |
| `JwtManager` | Emite tokens, valida los claims (`exp`, `nbf`, `iss`, `aud`) y revoca por `jti`. |
| `JwtGuard` | Driver `jwt` de Laravel; resuelve el usuario del token. |

Se registra en `App\Providers\JwtServiceProvider` y lo usa el guard `api` de
`config/auth.php`. Los parámetros están en `config/jwt.php`; la clave de firma
sale de `JWT_KEY` o, en su defecto, de `APP_KEY`.

Antes esto lo resolvía el paquete `JCarlosR/larajwt`, que se removió: estaba
clavado a `dev-master`, sin mantenimiento desde 2019, y llamaba a `starts_with()`,
un helper que Laravel eliminó en la versión 6 — con lo cual toda ruta autenticada
respondía 500. Se fue con él su única dependencia, `lcobucci/jwt` 3.3.1.

El formato del token no cambió, así que los que la app móvil ya tenía guardados
siguen siendo válidos.

> `logout` revoca el token anotando su `jti` en la caché hasta que expire. Como
> la caché es Redis y no persiste entre reinicios, un `docker compose down`
> vuelve a habilitar tokens deslogueados que todavía no hayan vencido.

Los tests de esta parte están en `tests/Unit/JwtTest.php` y no necesitan base de
datos ni contenedor:

```bash
docker compose exec app ./vendor/bin/phpunit --testsuite Unit --testdox
```

## Notas

- Los archivos de código se montan como bind mount, por lo que los cambios locales se reflejan sin reconstruir.
- La base de datos persiste en el volumen `mysql_data`.
- Para producción, usar `APP_DEBUG=false` y configurar un `APP_KEY` seguro.

## Licencia

MIT License.
