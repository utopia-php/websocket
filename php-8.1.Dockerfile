FROM composer:2.0 AS composer

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

FROM appwrite/utopia-base:php-8.1-0.1.0 AS final

RUN docker-php-ext-install sockets pcntl

COPY --from=composer /usr/local/src/vendor /usr/src/code/vendor

COPY ./src /usr/src/code/src
COPY ./tests /usr/src/code/tests

WORKDIR /usr/src/code

CMD ["tail", "-f", "/dev/null"]