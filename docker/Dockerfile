FROM composer AS composer

FROM php:7.4-fpm-alpine

ENV TZ Asia/Tokyo

RUN docker-php-source extract
RUN apk add --no-cache bash git nginx procps supervisor unzip zip
RUN apk add --no-cache --virtual=build-deps autoconf curl-dev gcc g++ make oniguruma-dev \
  && docker-php-ext-install -j$(nproc) opcache pcntl pdo pdo_mysql \
  && pecl install -o -f apcu msgpack redis \
  && docker-php-ext-enable apcu msgpack redis \
  && apk del build-deps \
  && docker-php-source delete \
  && find /var/log/ -type f -delete

RUN mkdir -p /run/nginx \
    mkdir -p /projects/app

COPY docker/app/files /
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN curl -sS -o /root/.git-prompt.sh https://raw.githubusercontent.com/git/git/master/contrib/completion/git-prompt.sh \
  && chmod +x /root/.git-prompt.sh

WORKDIR /projects/app

CMD ["/usr/bin/supervisord", "--nodaemon"]
