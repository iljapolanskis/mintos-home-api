FROM php:8.1-fpm

ARG TIMEZONE=UTC

# Set timezone
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    libonig-dev \
    libxslt1-dev \
    procps \
    && docker-php-ext-install \
    pdo_mysql \
    intl \
    zip \
    opcache \
    mbstring \
    xsl

# Install Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

WORKDIR /var/www/symfony

# Configure PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

# Configure PHP-FPM
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
