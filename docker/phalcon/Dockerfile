FROM mileschou/phalcon:7.4-cli

#
# install dependecies
#
RUN apt-get update && \
build_deps='wget zip unzip git libmagickwand-dev libmcrypt-dev libjpeg62-turbo-dev libpng-dev libfreetype6-dev libssl-dev openssl' && \
apt-get install -y $build_deps && \
apt-get clean && \
rm -rf /var/cache/apt/archives/* /var/lib/apt/lists/*

#
# install php extensions
#
RUN pecl install imagick
RUN docker-php-ext-enable imagick
RUN docker-php-ext-install -j$(nproc) pdo_mysql mysqli opcache bcmath pcntl
RUN pecl install redis && docker-php-ext-enable redis && pecl clear-cache

COPY composer.json composer.lock /app/
WORKDIR /app

#
# composer
#
ENV COMPOSER_ALLOW_SUPERUSER 1
COPY docker/composer/installer.sh /app/composer_installer.sh
RUN sh /app/composer_installer.sh
RUN rm -f /app/composer_installer.sh
