#!/bin/bash

PROJECT_DIR="$( cd "$( dirname $( dirname "${BASH_SOURCE[0]}") )" && pwd )"
PROJECT_NAME="$( basename ${PROJECT_DIR} )"
DOCKER_COMPOSE_YAML=${PROJECT_DIR}"/etc/dev/docker/docker-compose.yml"

function prepare {
    if [ ! -f ${PROJECT_DIR}/etc/dev/docker/nginx/server.crt ] || [ ! -f ${PROJECT_DIR}/etc/dev/docker/nginx/server.key ]; then
        echo "TLS certificate for HTTPS does not exist yet, creating..."
        openssl req -x509 \
            -nodes \
            -newkey rsa:4096 \
            -keyout ${PROJECT_DIR}/etc/dev/docker/nginx/server.key \
            -out ${PROJECT_DIR}/etc/dev/docker/nginx/server.crt \
            -days 365 \
            -subj '/CN=localhost' \
            -sha256
        echo "TLS certificate created."
    fi
}

function echo_configuration {
    echo "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~"
    echo "Running webserver on ports:"
    echo "HTTP:    "${HTTP_PORT}
    echo "HTTPS:   "${HTTPS_PORT}
    echo "MariaDB: "${MARIADB_PORT}
    echo "Mailhog: "${MAILHOG_PORT}
    echo ""
    echo "PHP version: "${PHP_VERSION}
    echo "MariaDB version: "${MARIADB_VERSION}
    echo "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~"
}

function build_container {
    prepare
    docker_compose_cmd build $@
}

function run_container {
    prepare
    echo_configuration
    docker_compose_cmd up $@
}

function start_container {
    prepare
    echo_configuration
    docker_compose_cmd up --no-start $@
    docker_compose_cmd start $@
}

function stop_container {
    docker_compose_cmd stop $@
}

function restart_container {
    docker_compose_cmd stop $@
    sleep 1
    docker_compose_cmd start $@
}


function remove_container {
    docker_compose_cmd rm $@
}

function get_logs {
    docker_compose_cmd logs $@
}

function reset_container {
    remove_container -s -f $@
    start_container $@
}

function docker_compose_cmd {
    docker-compose \
        -f ${DOCKER_COMPOSE_YAML} \
        -p ${PROJECT_NAME} \
        $@
}

function recreate_db {
    ${PROJECT_DIR}/bin/runInDev.sh bin/console doctrine:database:drop --force
    ${PROJECT_DIR}/bin/runInDev.sh bin/console doctrine:database:create
    ${PROJECT_DIR}/bin/runInDev.sh bin/console doctrine:schema:update --force
    ${PROJECT_DIR}/bin/runInDev.sh bin/console doctrine:fixtures:load --no-interaction
}

## start of the real program

set -a
. ${PROJECT_DIR}/etc/dev/docker/dev.docker.env
set +a

export PROJECT_DIR
export PROJECT_NAME

case "$1" in
    build)
        shift
        build_container $@
        ;;
    run)
        shift
        run_container $@
        ;;
    start)
        shift
        start_container $@
        ;;
    stop)
        shift
        stop_container $@
        ;;
    restart)
        shift
        restart_container $@
        ;;
    reset)
        shift
        reset_container $@
        ;;
    remove|rm)
        shift
        remove_container $@
        ;;
    log|logs)
        shift
        get_logs $@
        ;;
    recreate-db)
        shift
        recreate_db $@
        ;;
    *)
        echo "usage: start/stop/run/restart/build/reset/remove/tail"
        ;;
esac
