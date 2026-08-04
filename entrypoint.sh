#!/bin/bash

set -e

echo "Waiting for MySQL to be ready..."
until php -r 'new PDO("mysql:host=mysql;port=3306;dbname=laravel", "laravel", "secret");' >/dev/null 2>&1; do
    sleep 2
done

echo "MySQL is ready. Running setup..."

if [ ! -f .env ]; then
    cp .env.docker .env
fi

composer install --no-interaction --prefer-dist --no-progress

# Solo generar APP_KEY si no hay una: regenerarla en cada arranque
# invalidaria sesiones y cualquier dato encriptado.
if grep -q '^APP_KEY=$' .env; then
    php artisan key:generate --force
fi

php artisan migrate --force

# Los seeders no son idempotentes (chocan contra users_email_unique),
# asi que solo sembramos cuando la base esta vacia.
if [ "$(php -r 'try {
    $pdo = new PDO("mysql:host=mysql;port=3306;dbname=laravel", "laravel", "secret");
    echo $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() > 0 ? "no" : "yes";
} catch (Exception $e) { echo "yes"; }')" = "yes" ]; then
    echo "Seeding database..."
    php artisan db:seed --force
else
    echo "Database already seeded, skipping."
fi

echo "Setup complete. Application is ready."

exec "$@"
