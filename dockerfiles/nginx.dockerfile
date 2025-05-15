FROM nginx:stable-alpine

# Copiar configuración personalizada
COPY dockerfiles/nginx/default.conf /etc/nginx/conf.d/default.conf

# Establecer directorio de trabajo
WORKDIR /var/www

