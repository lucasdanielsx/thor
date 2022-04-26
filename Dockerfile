FROM php:8.1-fpm
 
# Copy composer.lock and composer.json
COPY composer.lock composer.json /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Install Additional dependencies
# RUN apt-get update && apt-get install -y \
#     build-base shadow vim curl \
#     php8 \
#     postgresql-dev

RUN apt-get update && apt-get install -y \
        git \
        zlib1g-dev \
        unzip \
        python \
        libzip-dev \
        libpq-dev \
        && ( \
            cd /tmp \
            && mkdir librdkafka \
            && cd librdkafka \
            && git clone https://github.com/edenhill/librdkafka.git . \
            && ./configure \
            && make \
            && make install \
        ) \
    && rm -r /var/lib/apt/lists/*

# PHP Extensions
RUN docker-php-ext-install -j$(nproc) zip \
    && pecl install rdkafka \
    && docker-php-ext-enable rdkafka

RUN apt-get install -y  \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql
RUN docker-php-ext-enable pdo_pgsql

# Install PHP Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


# Add UID '1000' to www-data
RUN usermod -u 1000 www-data

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www/html

# Change current user to www
USER www-data

# Expose port 9000 and start php-fpm server
EXPOSE 9000

CMD ["php-fpm"]