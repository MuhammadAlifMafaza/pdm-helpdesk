FROM docker.io/library/php:8.4-fpm-alpine

RUN apk add --no-cache \
        bash \
        freetype \
        icu-libs \
        libcurl \
        libjpeg-turbo \
        libpng \
        libzip \
        oniguruma \
        su-exec \
        unzip \
        zip \
    && apk add --no-cache --virtual .build-deps \
        curl-dev \
        freetype-dev \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libzip-dev \
        oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
    && apk del .build-deps \
    && rm -rf /tmp/* /var/cache/apk/*

COPY --from=docker.io/library/composer:2 /usr/bin/composer /usr/local/bin/composer
COPY docker/php/conf.d/app.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/php/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html
ENV COMPOSER_ALLOW_SUPERUSER=1

# Keep the dependency layer reusable when application files change.
COPY src/composer.json src/composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress \
        --no-scripts --no-autoloader

COPY src/ ./

# Package discovery runs during image build with a disposable key; the real
# APP_KEY is supplied only at runtime through src/.env.
RUN APP_ENV=production APP_DEBUG=false \
        APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= \
        composer dump-autoload --no-dev --optimize --no-interaction \
    && mkdir -p storage/app/public storage/framework/cache/data \
        storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/helpdesk-entrypoint
RUN chmod 0755 /usr/local/bin/helpdesk-entrypoint

EXPOSE 9000
ENTRYPOINT ["helpdesk-entrypoint"]
CMD ["php-fpm"]
