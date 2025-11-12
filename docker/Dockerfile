FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo_mysql zip

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug
# Copia configuração personalizada do Xdebug
COPY docker/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Define diretório de trabalho
WORKDIR /var/www/html

# Configura permissões
RUN chown -R www-data:www-data /var/www/html

# Expõe a porta do Apache
EXPOSE 80
