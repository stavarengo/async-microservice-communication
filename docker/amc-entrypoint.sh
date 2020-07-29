#!/usr/bin/env bash

_EXITING=0
function _wait() {
  wait $!
  local EXIT_CODE=$?
  [ "$_EXITING" == 1 ] && exit
  echo $EXIT_CODE
}
function _waitAll() {
  for JOB in $(jobs -p); do
    echo "Waiting $JOB..."
    wait $JOB
    echo "Job done: $JOB"
  done
}

function _sigint() {
  _EXITING=1
}

function _exit() {
  echo "Exiting..."
  _EXITING=1

  for JOB in $(jobs -p); do
    echo "   Sending SIGTERM to JOB $JOB..."
    kill -SIGTERM $JOB 2>/dev/null
  done
}

trap _sigint SIGINT SIGTERM SIGQUIT
trap _exit EXIT

if [ ! -f "$AMC_APP_DIR/composer.phar" ]; then
  cp /tmp/composer.phar "$AMC_APP_DIR/composer.phar"
fi

if [ ! -f "$AMC_APP_DIR/config/autoload/docker.local.php" ]; then
  ln -sf ../../docker/docker.local.php config/autoload/docker.local.php
fi

mkdir -p "$PKE_APP_DIR/var/logs"
php composer.phar install --no-progress

# first arg is not `-f` or `--some-option`
if [ "${1#-}" == "$1" ]; then
  exec "$@" &
else
  docker-php-entrypoint "$@" &
  _waitAll
fi
