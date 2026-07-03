FROM php:8.4-fpm-alpine AS base

# System dependencies
RUN apk add --no-cache \
    nginx \
    nodejs \
    npm \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    oniguruma-dev \
    libzip-dev \
    postgresql-dev \
    icu-dev \
    linux-headers

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies (cached layer)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Install Node dependencies (cached layer)
COPY package.json package-lock.json ./
RUN npm ci

# Copy full application (after deps to preserve cache)
COPY . .

# Remove Vite dev server hot file if present
RUN rm -f public/hot

# Build frontend assets
RUN npm run build

# Optimise autoloader
RUN composer dump-autoload --optimize

# Nginx & PHP config
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

# PHP-FPM pool upload limits
RUN echo "php_admin_value[upload_max_filesize] = 110M" >> /usr/local/etc/php-fpm.d/www.conf \
    && echo "php_admin_value[post_max_size] = 110M" >> /usr/local/etc/php-fpm.d/www.conf \
    && echo "php_admin_value[memory_limit] = 256M" >> /usr/local/etc/php-fpm.d/www.conf

# Storage permissions
RUN mkdir -p storage/logs storage/framework/{sessions,views,cache} \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 755 storage bootstrap/cache

COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080

CMD ["/start.sh"]
