FROM ghcr.io/roadrunner-server/roadrunner:2024.3.1 AS roadrunner

FROM ghcr.io/roqmeu/roadrunner-docker:php-8.3.15

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr

RUN set -eux; \
    update-ca-certificates; \
    pecl install xdebug-3.4.0; \
    rm -rf /tmp/pear; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=2.8.4

RUN set -eux; \
    apt-get remove -y \
        autoconf \
        dpkg-dev \
        file \
        g++ \
        gcc \
        make \
        pkg-config \
        re2c \
    ; \
    apt-get autoremove -y; \
    rm /usr/local/bin/docker-php-ext-* /usr/local/bin/docker-php-source

WORKDIR /opt/app
RUN set -eux; \
    chown www-data:www-data /opt/app; \
    mkdir -p -m 744 /opt/socket; \
    chown www-data:www-data /opt/socket
USER www-data
