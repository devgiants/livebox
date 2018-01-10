#!/usr/bin/env bash
set -o allexport
source ./.env
set +o allexport

# Create target dir if not exists already, and set it as current user (needed for container mapping)
mkdir -p ${HOST_RELATIVE_APP_PATH}
sudo chown -R ${HOST_USER}:${HOST_USER} ${HOST_RELATIVE_APP_PATH}
sudo chmod -R 775 ${HOST_RELATIVE_APP_PATH}

docker-compose up -d --build