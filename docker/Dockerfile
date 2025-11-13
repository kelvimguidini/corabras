# Usar imagem base PHP 8.3 com FPM
FROM php:8.2-fpm

# Atualiza e instala dependências necessárias
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    libicu-dev \
    libonig-dev \
    git \
    curl \
    && docker-php-ext-install \
    pdo_mysql \
    zip \
    intl \
    opcache

# 🧰 Instala o Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer

# Instala o Xdebug via PECL
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Copia a configuração personalizada do Xdebug
COPY docker/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia os arquivos do projeto
COPY . /var/www/html

# Instala dependências PHP via Composer
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Configura permissões
RUN chown -R www-data:www-data /var/www/html

# Exponha a porta correta (FPM usa 9000, não 80)
EXPOSE 9000

# Comando padrão (o PHP-FPM é iniciado automaticamente)
CMD ["php-fpm"]