FROM composer AS composer

FROM php:8-fpm-alpine

ARG APP_NAME
ENV APP_NAME $APP_NAME
ENV SUPERVISORD_USER root
ENV TZ Asia/Tokyo

RUN docker-php-source extract
RUN apk add --no-cache bash gettext git gmp-dev nginx procps supervisor unzip zip
RUN apk add --no-cache --virtual=build-deps autoconf curl-dev gcc g++ make oniguruma-dev \
  && docker-php-ext-install -j$(nproc) bcmath gmp opcache pcntl pdo pdo_mysql \
  && pecl install -o -f apcu igbinary msgpack redis \
  && docker-php-ext-enable apcu bcmath igbinary msgpack redis \
  && apk del build-deps \
  && docker-php-source delete \
  && find /var/log/ -type f -delete

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY docker/app/files /

RUN mkdir -p /run/nginx \
 && mkdir -p /var/www/$APP_NAME

WORKDIR /var/www/$APP_NAME

ENTRYPOINT ["/etc/docker/entrypoint.sh"]

CMD ["supervisord", "--nodaemon"]
