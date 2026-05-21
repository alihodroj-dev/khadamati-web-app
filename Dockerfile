FROM php:8.4-fpm-alpine

RUN apk add --no-cache nginx supervisor curl zip unzip git bash postgresql-dev nodejs npm \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY . .
RUN cp .env.example .env
RUN COMPOSER_MEMORY_LIMIT=-1 composer dump-autoload --optimize

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts && npm run build

RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]
