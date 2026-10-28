FROM docker.io/library/php:8.4-fpm-alpine

# pdo_mysql mbstring openssl tokenizer xml ctype json curl fileinfo gd bcmath
RUN apk add --no-cache \
        bash \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libxml2-dev \
        curl-dev \
        freetype \
        libpng \
        libjpeg-turbo \
        icu-libs \
        libzip \
        libcurl \
        zip \
        unzip \
        git \
        fcgi \
        su-exec \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
        exif \
        pcntl \
        curl \
    && apk del --no-cache freetype-dev libpng-dev libjpeg-turbo-dev icu-dev libzip-dev oniguruma-dev libxml2-dev curl-dev 2>/dev/null || true \
    && rm -rf /tmp/* /var/cache/apk/*

# Composer
COPY --from=docker.io/library/composer:2 /usr/bin/composer /usr/bin/composer

# php.ini overrides
COPY php/conf.d/app.ini /usr/local/etc/php/conf.d/app.ini

# www.conf overrides
COPY php/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY entrypoint.sh /usr/local/bin/helpdesk-entrypoint
RUN chmod +x /usr/local/bin/helpdesk-entrypoint

WORKDIR /var/www/html

EXPOSE 9000
ENTRYPOINT ["helpdesk-entrypoint"]
CMD ["php-fpm"]
