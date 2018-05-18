#!/bin/bash
export CONTAINER_NAME=shinagemariadb
export IMAGE_NAME=mariadb:latest
export DOCKER_RUN_ARGUMENTS="--net shinagedevnet -e MYSQL_ROOT_PASSWORD=root -e MYSQL_ALLOW_EMPTY_PASSWORD=false -e MYSQL_DATABASE=shinage-dev -p 3306:3306"

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

/bin/bash $DIR/controlDockerContainer.sh $1
