#!/bin/sh

/usr/local/bin/php bin/console doctrine:migrations:migrate --no-ansi --no-interaction
/usr/local/bin/php bin/console cache:warmup --no-ansi --no-interaction

#mkdir -p /app/data/pool

exec "$@"
