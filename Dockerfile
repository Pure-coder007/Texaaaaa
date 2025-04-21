# Use PHP 8.4 with FPM
FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev libonig-dev libxml2-dev libicu-dev \
    zip unzip curl git gnupg \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip exif pcntl bcmath intl opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create non-root user
RUN useradd -ms /bin/bash laraveluser

# Set working directory
WORKDIR /var/www/html

# Copy composer files and install dependencies early (better caching)
COPY composer.json composer.lock ./

# Copy the rest of the application code
COPY . .

# Fix ownership and permissions as root
RUN chown -R laraveluser:www-data /var/www/html && \
    chmod -R 775 /var/www/html && \
    mkdir -p storage/logs bootstrap/cache public/js/filament/forms/components && \
    touch storage/logs/laravel.log && \
    chown -R laraveluser:www-data storage bootstrap/cache public storage/logs/laravel.log && \
    chmod -R 775 storage bootstrap/cache public && \
    chmod 664 storage/logs/laravel.log && \
    # Explicitly set permissions for Filament directory
    chown -R laraveluser:www-data public/js/filament && \
    chmod -R 775 public/js/filament && \
    # Mark repository as safe for Git
    git config --global --add safe.directory /var/www/html

# Copy and make startup script executable as root
COPY start.sh /var/www/html/start.sh
RUN chmod +x /var/www/html/start.sh

# Switch to non-root user
USER laraveluser

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Build frontend
RUN npm ci --no-audit --prefer-offline && \
    npm run build && \
    rm -rf node_modules

# Laravel optimization
RUN php artisan config:clear && \
    php artisan cache:clear && \
    php artisan filament:clear-cached-components && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Expose the Laravel port (Render will map this automatically)
EXPOSE 8000

# Default command to run Laravel app
CMD ["./start.sh"]
