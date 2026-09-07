#!/bin/sh
set -eu

cd /app

mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

if [ ! -f vendor/autoload.php ]; then
    echo "Installing PHP dependencies..."
    composer install --no-interaction --prefer-dist --no-progress
fi

if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -qE '^APP_KEY=base64:.+' .env; then
    php artisan key:generate --force
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "Waiting for PostgreSQL..."
    i=0
    until php -r '
        try {
            new PDO(
                sprintf(
                    "pgsql:host=%s;port=%s;dbname=%s",
                    getenv("DB_HOST"),
                    getenv("DB_PORT"),
                    getenv("DB_DATABASE")
                ),
                getenv("DB_USERNAME"),
                getenv("DB_PASSWORD")
            );
            exit(0);
        } catch (Throwable $e) {
            exit(1);
        }
    '; do
        i=$((i + 1))
        if [ "$i" -ge 30 ]; then
            echo "PostgreSQL did not become ready in time." >&2
            exit 1
        fi
        sleep 2
    done

    php artisan migrate --force
fi

exec "$@"
