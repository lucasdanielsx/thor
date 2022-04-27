FROM php:8.1-fpm
 
# Copy composer.lock and composer.json
COPY composer.lock composer.json /var/www/html/

# Set working directory
WORKDIR /var/www/html

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

RUN usermod -u 1000 www-data
# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www/html

USER www-data

RUN chmod +x /var/www/html/docker-entrypoint.sh

RUN ls -la /var/www/html/docker-entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]