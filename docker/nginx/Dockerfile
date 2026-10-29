FROM docker.io/library/nginx:alpine

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY src/public /var/www/html/public

RUN mkdir -p /var/www/html/storage/app/public
