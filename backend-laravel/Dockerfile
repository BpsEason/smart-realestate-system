FROM php:8.3-fpm-alpine

# Install PHP extensions and dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    build-base \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    icu-dev \
    zlib-dev \
    gmp-dev \
    libintl \
    oniguruma-dev \
    mysql-client \
    imagemagick \
    php82-pecl-redis \
    nginx-mod-http-headers-more

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql opcache
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install -j$(nproc) gd
RUN docker-php-ext-install pcntl bcmath
RUN docker-php-ext-install intl zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . /var/www/html

# Set permissions for logs and application
# /var/log/supervisor 和 /var/log/nginx 目錄可寫入
RUN mkdir -p /var/log/supervisor /var/log/nginx && chmod -R 777 /var/log/supervisor /var/log/nginx
RUN chown -R www-data:www-data /var/www/html
RUN find /var/www/html -type d -exec chmod 775 {} +
RUN find /var/www/html -type f -exec chmod 664 {} +
RUN chmod -R 777 /var/www/html/storage # Allow Laravel to write to storage
RUN chmod -R 777 /var/www/html/bootstrap/cache # Allow Laravel to write to cache

# Copy Nginx and Supervisor configuration
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port
EXPOSE 80

# Start Supervisor (it will manage php-fpm and nginx)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
