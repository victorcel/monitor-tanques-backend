FROM php:8.4-fpm-alpine

# Arguments defined in docker-compose.yml
ARG user
ARG uid

# Install system dependencies (usando Alpine para reducir tamaño)
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    tzdata \
    imagemagick \
    imagemagick-dev

ENV TZ=America/Bogota

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath
RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd

# Get latest Composer (usando versión específica para estabilidad)
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN addgroup -g $uid -S $user && \
    adduser -u $uid -S $user -G www-data && \
    mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

USER $user

