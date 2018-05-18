#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

export CONTAINER_NAME=shinagenginx
export IMAGE_NAME=nginx:alpine

LOCAL_DIR=$(dirname $DIR)
LOCAL_CONFIG=${LOCAL_DIR}/etc/nginx_docker.conf
echo $LOCAL_CONFIG

export DOCKER_RUN_ARGUMENTS="--net shinagedevnet -p 8081:80 -v ${LOCAL_DIR}:/usr/share/nginx/html:rw -v ${LOCAL_CONFIG}:/etc/nginx/nginx.conf"

/bin/bash $DIR/controlDockerContainer.sh $1
