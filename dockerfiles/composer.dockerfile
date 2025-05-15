FROM composer:2.8.8

# Install the required PHP extensions
RUN apk add --no-cache $PHPIZE_DEPS \
    && apk del --purge $PHPIZE_DEPS \
    && apk add --no-cache libpng libpng-dev \
    && docker-php-ext-install gd \
    && apk del libpng-dev

RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel

USER laravel

WORKDIR /var/www

ENTRYPOINT [ "composer","install" ]
