FROM php:8.2-apache

# Включаем mod_rewrite
RUN a2enmod rewrite

# PHP-расширения (curl нужен для запросов к FastAPI)
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && rm -rf /var/lib/apt/lists/*

# Конфигурация Apache — разрешаем .htaccess и устанавливаем DocumentRoot
COPY apache.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Устанавливаем права доступа
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

EXPOSE 80
