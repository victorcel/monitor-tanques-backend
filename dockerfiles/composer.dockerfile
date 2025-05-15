FROM composer:2.6

# Reduciendo capas innecesarias
WORKDIR /var/www

# Entrypoint más flexible para permitir otros comandos además de install
ENTRYPOINT ["composer"]
CMD ["install", "--no-interaction", "--optimize-autoloader"]
