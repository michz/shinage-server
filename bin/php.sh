#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

#export CONTAINER_NAME=shinagephp
#export IMAGE_NAME=php:7.2-apache

#LOCAL_DIR=$(dirname $DIR)
#LOCAL_CONFIG=$(dirname $DIR)/etc/nginx_docker.conf
#echo $LOCAL_CONFIG

#LOCAL_DIR=$(dirname $DIR)
#LOCAL_PARAMETERS=$(dirname $DIR)/app/config/parameters.yml.dev.docker
#export DOCKER_RUN_ARGUMENTS="--net shinagedevnet -p 8081:80 -v ${LOCAL_DIR}:/var/www/html:rw -v ${LOCAL_PARAMETERS}:/var/www/html/app/config/parameters.yml:ro"
#
#/bin/bash $DIR/controlDockerContainer.sh $1


DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

export CONTAINER_NAME=shinagephp
export IMAGE_NAME=php:7.2-fpm-alpine

LOCAL_DIR=$(dirname $DIR)
#LOCAL_CONFIG=$(dirname $DIR)/etc/nginx_docker.conf
#echo $LOCAL_CONFIG

export DOCKER_RUN_ARGUMENTS="--net shinagedevnet -v ${LOCAL_DIR}:/usr/share/nginx/html:rw -p 9000:9000"

/bin/bash $DIR/controlDockerContainer.sh $1
