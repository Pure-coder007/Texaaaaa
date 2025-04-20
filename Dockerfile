FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    zip \
    unzip \
    curl \
    git \
    gnupg \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip exif pcntl bcmath intl opcache

# Install Composer (from official Composer image)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create a non-root user to avoid running as root in the container
RUN useradd -ms /bin/bash laraveluser

# Set working directory and switch to laraveluser
WORKDIR /var/www/html
USER laraveluser

# Copy application code first (this ensures artisan is copied before composer install)
COPY . .

# Copy composer files (this step is crucial for Composer to cache dependencies)
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Install JS dependencies and build
RUN npm ci --no-audit --prefer-offline && \
    npm run build && \
    rm -rf node_modules

# Set correct permissions (storage and cache folders)
RUN chown -R laraveluser:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Expose port
EXPOSE 8000

# Start Laravel (clear caches, optimize, then serve)
CMD ["sh", "-c", "php artisan optimize:clear && php artisan optimize && php artisan serve --host=0.0.0.0 --port=8000"]
