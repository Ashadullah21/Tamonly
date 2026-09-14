FROM php:8.2-apache

# Set environment flags
ENV DEBIAN_FRONTEND=noninteractive
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install system dependencies & libraries
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libpq-dev \
    libsqlite3-dev \
    nodejs \
    npm \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install required PHP extensions for Laravel, MySQL, PostgreSQL, and SQLite
RUN docker-php-ext-install \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath \
    gd \
    opcache

# Enable required Apache modules
RUN a2enmod rewrite headers deflate

# Copy Composer from official composer image
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy Apache and PHP configurations
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Copy composer definition and install PHP dependencies (layer caching)
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy frontend definitions and install npm packages
COPY package.json package-lock.json* ./
RUN npm install

# Copy application source code
COPY . .

# Generate optimized autoloader and build production frontend assets
RUN composer dump-autoload --optimize --no-dev
RUN npm run build && rm -rf node_modules

# Ensure entry script has Linux LF line endings and executable rights
RUN sed -i 's/\r$//' docker/entry.sh && chmod +x docker/entry.sh

# Set directory permissions for Apache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose default HTTP port
EXPOSE 80

# Execute entrypoint script
CMD ["/var/www/html/docker/entry.sh"]
