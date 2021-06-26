FROM composer AS composer

FROM php:8-fpm-alpine

ARG APP_NAME
ENV APP_NAME $APP_NAME

RUN docker-php-source extract
RUN apk add --no-cache bash gettext git gmp-dev nginx procps supervisor unzip zip
RUN apk add --no-cache --virtual=build-deps autoconf curl-dev gcc g++ make oniguruma-dev \
  && docker-php-ext-install -j$(nproc) bcmath gmp opcache pcntl pdo pdo_mysql \
  && pecl install -o -f apcu igbinary msgpack redis \
  && docker-php-ext-enable apcu bcmath igbinary msgpack redis \
  && apk del build-deps \
  && docker-php-source delete \
  && find /var/log/ -type f -delete

COPY docker/app/files /
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN mkdir -p /run/nginx \
 && mkdir -p /var/www/$APP_NAME

RUN curl -sS -o /root/.git-prompt.sh https://raw.githubusercontent.com/git/git/master/contrib/completion/git-prompt.sh \
  && chmod +x /root/.git-prompt.sh

WORKDIR /var/www/$APP_NAME

ENTRYPOINT ["/docker/entrypoint.sh"]

CMD ["/usr/bin/supervisord", "--nodaemon"]
