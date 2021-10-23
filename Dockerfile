FROM php:7.4-cli

RUN docker-php-ext-install pdo pdo_mysql
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY . /app
WORKDIR /app

ENTRYPOINT ["./scripts/entrypoint.sh"]