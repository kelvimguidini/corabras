# Imagem base PHP 8.2 com FPM
FROM php:8.2-fpm

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    libicu-dev \
    git \
    curl \
    libfreetype6-dev \
    libjpeg-dev \
    libpng-dev \
    && docker-php-ext-install pdo_mysql zip intl opcache

# Configura e instala a extensão GD com JPEG/PNG/Freetype
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Instala o Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer

# Instala o Xdebug via PECL
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Copia configuração do Xdebug
COPY docker/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Diretório de trabalho padrão
WORKDIR /var/www/html

# Ajusta permissões
RUN chown -R www-data:www-data /var/www/html

# Exposição da porta FPM
EXPOSE 9000

CMD ["php-fpm"]
