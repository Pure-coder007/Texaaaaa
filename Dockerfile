FROM php:8.2-fpm  # Changed from cli to fpm for better web server compatibility

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \  # Moved up with other dev libs
    zip \
    unzip \
    curl \
    git \
    && curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \  # Better Node.js installation
    && apt-get install -y nodejs \
    && npm install -g npm@latest

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip exif pcntl bcmath intl opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better layer caching
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy everything else
COPY . .

# Install JS dependencies and build
RUN npm ci --no-audit --prefer-offline \  # ci is better for production than install
    && npm run build \
    && rm -rf node_modules  # Remove dev dependencies after build

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Expose port
EXPOSE 8000

# Health check
HEALTHCHECK --interval=30s --timeout=3s \
    CMD curl -f http://localhost:8000 || exit 1

# Start Laravel with production optimizations
CMD php artisan optimize:clear && \
    php artisan optimize && \
    php artisan serve --host=0.0.0.0 --port=8000