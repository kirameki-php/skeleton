FROM composer AS composer
FROM toyama4649/kirameki-php-cli:latest AS kirameki

FROM dunglas/frankenphp:1-php8.4-alpine

ARG APP_NAME
ENV APP_NAME=$APP_NAME
ENV TZ=Asia/Tokyo
ENV FRANKENPHP_CONFIG="worker /var/www/${APP_NAME}/run.php"

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY --from=kirameki /etc/profile.d /etc/profile.d

WORKDIR /var/www/$APP_NAME

RUN set -e \
    && apk add --no-cache bash coreutils git icu-libs libstdc++ libxml2 tzdata \
    && apk add --no-cache --virtual=.build-deps autoconf g++ icu-dev make \
    && install-php-extensions bcmath intl pcntl pdo_mysql \
    && pecl install apcu \
    && pecl install ast \
    && pecl install igbinary \
    && pecl install pcov \
    && pecl install redis \
    && apk del .build-deps \
    && mkdir -p /src

COPY --from=kirameki /usr/local/etc/php/ /usr/local/etc/php/
