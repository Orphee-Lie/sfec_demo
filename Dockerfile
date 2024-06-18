FROM php:8.2

RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    default-mysql-client

RUN docker-php-source extract && \
    apt-get install -y \
    libpq-dev \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd && \
    docker-php-ext-install \
    pdo_mysql && \
    docker-php-source delete

COPY ./configdocker/php.ini /usr/local/etc/php/

# Installe Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Définit le répertoire de travail
WORKDIR /var/www/html

# Copie les fichiers du projet dans le conteneur
COPY . /var/www/html

# Installe les dépendances PHP via Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN export PATH="$HOME/.symfony5/bin:$PATH"
RUN composer install --ignore-platform-reqs

RUN php bin/console lexik:jwt:generate-keypair --overwrite
# RUN    php artisan route:cache
# RUN    chmod 777 -R /var/www/html/storage/
RUN    chown -R www-data:www-data /var/www/
# ENV API_URL=http://localhost:10016
# ENV API_TOKEN="715763b2-87be-404b-bb86-b66dfd879e63"

# Expose le port 8001 pour le serveur artisan
EXPOSE 8000

# Exécute la commande artisan serve lors du démarrage du conteneur
# CMD ["symfony", "server:start", "--port=8002"]
CMD php -S 0.0.0.0:8000 -t public


# FROM php:8.0-apache-buster as production

# ENV APP_ENV=production
# ENV APP_DEBUG=false

# # RUN docker-php-ext-configure opcache --enable-opcache && \
# #     docker-php-ext-install pdo pdo_mysql
# # COPY docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# COPY --from=build /app /var/www/html
# # COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
# COPY .env.prod /var/www/html/.env

# RUN php artisan config:cache && \
#     php artisan route:cache && \
#     chmod 777 -R /var/www/html/storage/ && \
#     chown -R www-data:www-data /var/www/ && \
#     a2enmod rewrite
