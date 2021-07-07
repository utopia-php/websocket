FROM php:8.0-cli-alpine3.13

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN docker-php-ext-configure pcntl --enable-pcntl

RUN docker-php-ext-install pcntl