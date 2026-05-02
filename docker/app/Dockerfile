# syntax=docker/dockerfile:1.7

###############################################################################
# Stage: base
# - PHP 8.2 FPM on Alpine
# - Oracle Instant Client 21 + oci8 PECL extension
# - Common Laravel deps (intl, mbstring, bcmath, zip, opcache, pdo, pcntl)
# - SQLite (used by tests; cheap to ship)
###############################################################################
FROM php:8.2.20-fpm-alpine3.20 AS base

ARG TARGETARCH

# Runtime libs needed by PHP extensions and Oracle Instant Client.
# - libaio, libnsl: required by Oracle Instant Client
# - gcompat / libc6-compat: glibc shim so the Oracle (glibc) binaries work on musl
RUN apk add --no-cache \
    bash \
    curl \
    ca-certificates \
    tini \
    libaio \
    libnsl \
    libc6-compat \
    gcompat \
    icu-libs \
    libpng \
    libjpeg-turbo \
    libxml2 \
    sqlite-libs \
    oniguruma \
    freetype \
    libzip \
    tzdata

ENV TZ=America/Sao_Paulo

# Build deps — kept under a virtual package so we can drop them at the end.
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    autoconf \
    g++ \
    make \
    linux-headers \
    libaio-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libxml2-dev \
    icu-dev \
    sqlite-dev \
    oniguruma-dev \
    freetype-dev \
    libzip-dev \
    unzip

###
### Oracle Instant Client (basic + sdk).
### Required by oci8 build & runtime.
### Pinned to 21.13 — bump explicitly when needed (no 'latest').
###
ARG ORACLE_VERSION=21.13.0.0.0
ARG ORACLE_DIR=21_13
RUN set -eux; \
    if [ "${TARGETARCH:-amd64}" != "amd64" ]; then \
        echo "Oracle Instant Client only supports amd64 in this Dockerfile. arch=${TARGETARCH}"; exit 1; \
    fi; \
    mkdir -p /opt/oracle && cd /opt/oracle; \
    curl -fsSLO "https://download.oracle.com/otn_software/linux/instantclient/2113000/instantclient-basic-linux.x64-${ORACLE_VERSION}dbru.zip"; \
    curl -fsSLO "https://download.oracle.com/otn_software/linux/instantclient/2113000/instantclient-sdk-linux.x64-${ORACLE_VERSION}dbru.zip"; \
    unzip -q 'instantclient-basic-*.zip'; \
    unzip -q 'instantclient-sdk-*.zip'; \
    rm -f *.zip; \
    mv "instantclient_${ORACLE_DIR}" instantclient; \
    ln -sf /opt/oracle/instantclient/libclntsh.so.21.1 /opt/oracle/instantclient/libclntsh.so; \
    # IMPORTANT: this file REPLACES the musl default search path. We must keep the
    # defaults (/lib, /usr/local/lib, /usr/lib) or system binaries (tar, etc.) break.
    printf '%s\n' /lib /usr/local/lib /usr/lib /opt/oracle/instantclient \
        > /etc/ld-musl-x86_64.path; \
    ldconfig /etc/ld-musl-x86_64.path 2>/dev/null || true

ENV LD_LIBRARY_PATH=/opt/oracle/instantclient
ENV ORACLE_HOME=/opt/oracle/instantclient
ENV NLS_LANG=AMERICAN_AMERICA.AL32UTF8

###
### Core PHP extensions
###
RUN docker-php-ext-configure intl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        intl \
        bcmath \
        mbstring \
        zip \
        opcache \
        pcntl \
        gd \
        pdo \
        pdo_mysql

###
### oci8 (Oracle) — pecl prompts for the instant client path; feed it via stdin.
###
RUN printf "instantclient,/opt/oracle/instantclient\n" | pecl install oci8-3.3.0 \
    && docker-php-ext-enable oci8

###
### Composer (pinned). Removed from prod stage at the end.
###
COPY --from=composer:2.7.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

###
### Drop build deps to slim the image.
###
RUN apk del .build-deps \
    && rm -rf /tmp/* /var/cache/apk/* /usr/local/lib/php/test /usr/local/lib/php/doc

###
### App user UID 1000 to match host dev user. Replace the stock www-data.
###
RUN deluser www-data \
    && addgroup -g 1000 -S www-data \
    && adduser -u 1000 -S -D -G www-data www-data \
    && mkdir -p /var/www/html /var/www/.composer \
    && chown -R www-data:www-data /var/www


###############################################################################
# Stage: dev
# - Adds Xdebug, leaves Composer in place
# - Code is bind-mounted from the host via docker-compose (no COPY here)
###############################################################################
FROM base AS dev

# Xdebug needs build deps again — install scoped, drop after.
RUN apk add --no-cache --virtual .xdebug-build $PHPIZE_DEPS linux-headers \
    && pecl install xdebug-3.3.2 \
    && docker-php-ext-enable xdebug \
    && apk del .xdebug-build \
    && rm -rf /tmp/pear /tmp/* /var/cache/apk/*

COPY docker/app/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/app/php-xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini
COPY docker/app/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER www-data
EXPOSE 9000
ENTRYPOINT ["/sbin/tini", "--", "/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm", "-F"]


###############################################################################
# Stage: prod
# - No Xdebug, no test files (.dockerignore handles that)
# - composer install --no-dev, then composer is removed from the image
###############################################################################
FROM base AS prod

COPY docker/app/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/app/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Code is copied in. If composer.json doesn't exist yet (early bootstrap),
# the image still builds — useful while the project is being scaffolded.
COPY --chown=www-data:www-data . /var/www/html

RUN if [ -f composer.json ]; then \
        composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist; \
        composer clear-cache; \
    fi \
    && rm -f /usr/bin/composer

USER www-data
EXPOSE 9000
ENTRYPOINT ["/sbin/tini", "--", "/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm", "-F"]
