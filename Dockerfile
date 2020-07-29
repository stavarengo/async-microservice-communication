FROM php:7.4.8-buster

ENV AMC_APP_DIR=/usr/src/async-microservice-communication

WORKDIR "$AMC_APP_DIR"

RUN DEBIAN_FRONTEND=noninteractive apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y unzip libpq-dev && \
    docker-php-ext-install sockets pdo pdo_pgsql && \
    rm -rfv "$AMC_APP_DIR/*" && \
    curl -sS -o "/tmp/composer.phar" https://getcomposer.org/download/1.10.9/composer.phar && \
    ln -s "/tmp/composer.phar" "$AMC_APP_DIR/composer.phar"

COPY composer.json "$AMC_APP_DIR/composer.json"
COPY composer.lock "$AMC_APP_DIR/composer.lock"

RUN php composer.phar install --no-progress

COPY ./ "$AMC_APP_DIR/"
COPY ./docker/amc-entrypoint.sh /usr/local/bin/amc-entrypoint.sh

ENTRYPOINT ["amc-entrypoint.sh"]
