#!/bin/bash


PROJECT_DIR="$( cd "$( dirname $( dirname "${BASH_SOURCE[0]}") )" && pwd )"
PROJECT_NAME="$( basename ${PROJECT_DIR} )"
PHP_CONTAINER_NAME="${PROJECT_NAME}-php-1"

docker exec -e XDEBUG_MODE=coverage --workdir /usr/share/nginx/html -it ${PHP_CONTAINER_NAME} $@
