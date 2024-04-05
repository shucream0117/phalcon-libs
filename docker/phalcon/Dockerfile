FROM php:7.4-cli AS development

#
# Phalconインストール
# @see https://github.com/MilesChou/docker-phalcon
#

ARG PSR_VERSION=1.0.0
ARG PHALCON_VERSION=4.0.6
ARG PHALCON_EXT_PATH=php7/64bits

RUN set -xe && \
    # Download PSR, see https://github.com/jbboehr/php-psr
    curl -LO https://github.com/jbboehr/php-psr/archive/v${PSR_VERSION}.tar.gz && \
    tar xzf ${PWD}/v${PSR_VERSION}.tar.gz && \
    # Download Phalcon
    curl -LO https://github.com/phalcon/cphalcon/archive/v${PHALCON_VERSION}.tar.gz && \
    tar xzf ${PWD}/v${PHALCON_VERSION}.tar.gz && \
    docker-php-ext-install -j $(getconf _NPROCESSORS_ONLN) \
        ${PWD}/php-psr-${PSR_VERSION} \
        ${PWD}/cphalcon-${PHALCON_VERSION}/build/${PHALCON_EXT_PATH} \
    && \
    # Remove all temp files
    rm -r \
        ${PWD}/v${PSR_VERSION}.tar.gz \
        ${PWD}/php-psr-${PSR_VERSION} \
        ${PWD}/v${PHALCON_VERSION}.tar.gz \
        ${PWD}/cphalcon-${PHALCON_VERSION} \
    && \
    php -m

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
