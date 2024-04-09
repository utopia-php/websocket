FROM composer:2.0 as composer

ARG TESTING=false
ENV TESTING=$TESTING

WORKDIR /usr/local/src/

COPY composer.lock /usr/local/src/
COPY composer.json /usr/local/src/

RUN composer install \
    --ignore-platform-reqs \
    --optimize-autoloader \
    --no-plugins \
    --no-scripts \
    --prefer-dist

FROM php:8.3-cli-alpine3.19 as compile

RUN \
  apk add --no-cache --virtual .deps \
  linux-headers \
  make \
  automake \
  autoconf \
  gcc \
  g++ \
  git \
  openssl-dev \
  curl-dev

RUN docker-php-ext-install sockets

ENV PHP_SWOOLE_VERSION="v5.1.2"

FROM compile AS swoole
RUN \
  git clone --depth 1 --branch $PHP_SWOOLE_VERSION https://github.com/swoole/swoole-src.git && \
  cd swoole-src && \
  phpize && \
  ./configure --enable-sockets --enable-http2 --enable-openssl --enable-swoole-curl && \
  make && make install && \
  cd ..

FROM php:8.3-cli-alpine3.19 as final

RUN apk update && apk add --no-cache \
  linux-headers \
  make \
  automake \
  autoconf \
  gcc \
  g++ \
  curl-dev \
  libstdc++

RUN docker-php-ext-install sockets pcntl

COPY --from=composer /usr/local/src/vendor /usr/src/code/vendor
COPY --from=swoole /usr/local/lib/php/extensions/no-debug-non-zts-20230831/swoole.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/

RUN echo extension=swoole.so >> /usr/local/etc/php/conf.d/swoole.ini

COPY ./src /usr/src/code/src
COPY ./tests /usr/src/code/tests

WORKDIR /usr/src/code

CMD ["tail", "-f", "/dev/null"]