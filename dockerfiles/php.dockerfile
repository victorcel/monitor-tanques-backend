FROM php:8.4-fpm

# Arguments defined in docker-compose1.yml
ARG user
ARG uid

# Install system dependencies
RUN apt-get update && apt-get install -y \
    autoconf \
    pkg-config \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libgd-dev \
    jpegoptim optipng pngquant gifsicle \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    tzdata\
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    libssl-dev \
    imagemagick \
    libmagickwand-dev

ENV TZ=America/Bogota

# Install ImageMagick extension
RUN pecl install imagick && docker-php-ext-enable imagick

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*


# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd
RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

USER $user

