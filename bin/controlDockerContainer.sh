#!/bin/bash

if [ -z "$CONTAINER_NAME" ]; then
    echo "You must define \$CONTAINER_NAME to use this script. Abort." 1>&2
    exit 1
fi

if [ -z "$IMAGE_NAME" ]; then
    echo "You must define \$IMAGE_NAME to use this script. Abort." 1>&2
    exit 2
fi

function start_container {
    if [ ! "$(docker ps -q -f name=$CONTAINER_NAME)" ]; then
        if [ "$(docker ps -aq -f status=exited -f name=$CONTAINER_NAME)" ]; then
            echo "Starting existing container $CONTAINER_NAME..."
            docker start $CONTAINER_NAME
            echo "Done."
        else
            echo "Creating container $CONTAINER_NAME..."
            docker run -d \
                --name $CONTAINER_NAME \
                $DOCKER_RUN_ARGUMENTS \
                $IMAGE_NAME
            echo "Done."
        fi
    else
        echo "$CONTAINER_NAME is already running."
    fi
}

function stop_container {
    if [  "$(docker ps -q -f name=$CONTAINER_NAME)" ]; then
        echo "Stopping $CONTAINER_NAME..."
        docker stop $CONTAINER_NAME
        echo "Done."
    else
        echo "$CONTAINER_NAME is not running."
    fi
}

function remove_container {
    if [ "$(docker ps -aq -f name=$CONTAINER_NAME)" ]; then
        echo "Removing $CONTAINER_NAME..."
        docker stop $CONTAINER_NAME
        docker rm $CONTAINER_NAME
        echo "Done."
    fi
}

function reset_container {
    remove_container
    start_container
}

function tail_logs {
    if [  "$(docker ps -q -f name=$CONTAINER_NAME)" ]; then
        docker logs --tail 50 -f $CONTAINER_NAME
    else
        echo "$CONTAINER_NAME is not running."
    fi
}

SCRIPTNAME=$(basename $0)

case "$1" in
    start)
        start_container
        ;;
    stop)
        stop_container
        ;;
    reset)
        reset_container
        ;;
    remove)
        remove_container
        ;;
    tail)
        tail_logs
        ;;
    *)
        echo "Usage: $SCRIPTNAME start/stop/reset/remove/tail"
        ;;
esac
