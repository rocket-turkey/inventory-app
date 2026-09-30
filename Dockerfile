FROM composer:2 AS dependencies
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --optimize-autoloader

FROM node:22-alpine AS styles
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY src/styles/ ./src/styles/
RUN npm run build:css

FROM php:8.3-apache
RUN docker-php-ext-install pdo_mysql
RUN printf 'display_errors=Off\nlog_errors=On\n' > /usr/local/etc/php/conf.d/app.ini
ENV APACHE_DOCUMENT_ROOT=/var/www/app/public
RUN sed -ri 's!/var/www/html!/var/www/app/public!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN printf '<Directory /var/www/app/public>\nFallbackResource /index.php\n</Directory>\n' > /etc/apache2/conf-available/app-routing.conf && a2enconf app-routing
COPY src/ /var/www/app/
COPY --from=dependencies /app/vendor/ /var/www/app/vendor/
COPY --from=styles /app/build/app.css /var/www/app/public/app.css
