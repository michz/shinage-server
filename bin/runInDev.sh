#!/bin/bash


PROJECT_DIR="$( cd "$( dirname $( dirname "${BASH_SOURCE[0]}") )" && pwd )"
PROJECT_NAME="$( basename ${PROJECT_DIR} )"
PHP_CONTAINER_NAME="${PROJECT_NAME}_php_1"

docker exec --workdir /usr/share/nginx/html -it ${PHP_CONTAINER_NAME} $@
