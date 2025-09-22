FROM debian:bookworm-slim AS build

ARG BUILD_DEPS="ca-certificates wget xz-utils gnupg"
ARG PHPIZE_DEPS="autoconf dpkg-dev file g++ gcc libc-dev make pkg-config re2c"
ARG PHP_BUILD_DEPS="libbrotli-dev libevent-dev libpsl-dev libssl-dev libzip-dev zlib1g-dev libargon2-dev libonig-dev libreadline-dev libsodium-dev libxml2-dev libicu-dev libc-client2007e-dev libkrb5-dev libpcre2-dev libpq-dev libmemcached-dev librabbitmq-dev libyaml-dev"

RUN set -eux; \
# prevent Debian's PHP packages from being installed
	{ \
		echo 'Package: php*'; \
		echo 'Pin: release *'; \
		echo 'Pin-Priority: -1'; \
	} > /etc/apt/preferences.d/no-debian-php; \
	\
# prevent Debian's cURL packages from being installed
	{ \
		echo 'Package: curl'; \
		echo 'Pin: release *'; \
		echo 'Pin-Priority: -1'; \
	} > /etc/apt/preferences.d/no-debian-curl; \
	\
# install build dependencies \
	apt-get update; \
	apt-get install -y --no-install-recommends $PHP_BUILD_DEPS $PHPIZE_DEPS $BUILD_DEPS; \
	rm -rf /var/lib/apt/lists/*; \
	\
# make workdir
	mkdir -p /var/www/html

# Apply stack smash protection to functions using local buffers and alloca()
# Make PHP's main executable position-independent (improves ASLR security mechanism, and has no performance impact on x86_64)
# Enable optimization (-O2)
# Enable linker optimization (this sorts the hash buckets to improve cache locality, and is non-default)
# https://github.com/docker-library/php/issues/272
# -D_LARGEFILE_SOURCE and -D_FILE_OFFSET_BITS=64 (https://www.php.net/manual/en/intro.filesystem.php)
ARG PHP_CFLAGS="-fstack-protector-strong -fpic -fpie -g0 -O2 -D_LARGEFILE_SOURCE -D_FILE_OFFSET_BITS=64"
ARG PHP_CPPFLAGS="$PHP_CFLAGS"
ARG PHP_LDFLAGS="-Wl,-O1 -pie"
ARG PHP_INI_DIR="/usr/local/etc/php"

ARG GPG_KEYS="1198C0117593497A5EC5C199286AF1F9897469DC C28D937575603EB4ABB725861C0779DC5C0A9DE4 AFD8691FDAEDF03BDF6E460563F15A9B715376CA"

ARG PHP_VERSION="8.3.25"
ARG PHP_URL="https://www.php.net/distributions/php-8.3.25.tar.xz"
ARG PHP_ASC_URL="https://www.php.net/distributions/php-8.3.25.tar.xz.asc"
ARG PHP_SHA256="187b61bb795015adacf53f8c55b44414a63777ec19a776b75fb88614506c0d37"

RUN set -eux; \
	\
	mkdir -p "$PHP_INI_DIR/conf.d"; \
	mkdir -p /usr/src; \
	cd /usr/src; \
	\
	wget -q -O php.tar.xz "$PHP_URL"; \
	\
	if [ -n "$PHP_SHA256" ]; then \
		echo "$PHP_SHA256 *php.tar.xz" | sha256sum -c -; \
	fi; \
	\
	if [ -n "$PHP_ASC_URL" ]; then \
		wget -q -O php.tar.xz.asc "$PHP_ASC_URL"; \
		export GNUPGHOME="$(mktemp -d)"; \
		for key in $GPG_KEYS; do \
			gpg --batch --keyserver keyserver.ubuntu.com --recv-keys "$key"; \
		done; \
		gpg --batch --verify php.tar.xz.asc php.tar.xz; \
		gpgconf --kill all; \
		rm -rf "$GNUPGHOME"; \
	fi

# Build curl with c-ares resolver
RUN set -eux; \
	\
	export CFLAGS="-fstack-protector-strong -fpic -fpie -g0 -O2";  \
	export CXXFLAGS="$CFLAGS"; \
	export LDFLAGS="-Wl,-O1 -pie"; \
	\
	cd /usr/src; \
	wget -q -O ./c-ares.gz "https://github.com/c-ares/c-ares/releases/download/v1.34.5/c-ares-1.34.5.tar.gz"; \
	tar -xzf ./c-ares.gz; \
	cd ./c-ares-1.34.5; \
	./configure --disable-dependency-tracking --disable-tests; \
	make -j "$(nproc)"; \
	make install; \
	ldconfig; \
	\
	cd /usr/src; \
	wget -q -O ./curl.gz "https://github.com/curl/curl/releases/download/curl-8_12_1/curl-8.12.1.tar.gz"; \
	tar -xzf ./curl.gz; \
	cd ./curl-8.12.1; \
	./configure --enable-ares=/usr/local --enable-versioned-symbols --with-brotli --with-openssl --with-libpsl --disable-manual --disable-docs; \
	make -j "$(nproc)"; \
	make install; \
	ldconfig; \
	\
	/usr/local/bin/curl --version; \
	ln -sf /usr/local/bin/curl /usr/bin/curl; \
	ln -sT /usr/local/include/curl /usr/include/curl; \
	\
	rm -rf /usr/src/curl* /usr/src/c-ares*

COPY docker-php-* /usr/local/bin/

# Set static PHP pecl extensions version
ARG PHP_EXT_AMQP_VERSION="2.1.2"
ARG PHP_EXT_DS_VERSION="1.6.0"
ARG PHP_EXT_EVENT_VERSION="3.1.4"
ARG PHP_EXT_EXCIMER_VERSION="1.2.5"
ARG PHP_EXT_IGBINARY_VERSION="3.2.16"
ARG PHP_EXT_MEMCACHED_VERSION="3.3.0"
ARG PHP_EXT_PROTOBUF_VERSION="4.31.1"
ARG PHP_EXT_REDIS_VERSION="6.2.0"
ARG PHP_EXT_XDEBUG_VERSION="3.4.5"
ARG PHP_EXT_YAML_VERSION="2.2.4"

RUN set -eux; \
	\
	export \
		CFLAGS="$PHP_CFLAGS" \
		CPPFLAGS="$PHP_CPPFLAGS" \
		LDFLAGS="$PHP_LDFLAGS" \
		PHP_BUILD_PROVIDER='https://github.com/roqmeu/roadrunner-docker' \
		PHP_UNAME='Linux - Docker' \
	; \
	docker-php-source extract; \
	cd /usr/src/php; \
	gnuArch="$(dpkg-architecture --query DEB_BUILD_GNU_TYPE)"; \
	debMultiarch="$(dpkg-architecture --query DEB_BUILD_MULTIARCH)"; \
# https://bugs.php.net/bug.php?id=74125
	if [ ! -d /usr/include/curl ]; then \
		ln -sT "/usr/include/$debMultiarch/curl" /usr/local/include/curl; \
	fi; \
	\
# download static PHP pecl extensions
	docker-php-ext-install-pecl-static amqp $PHP_EXT_AMQP_VERSION; \
	docker-php-ext-install-pecl-static ds $PHP_EXT_DS_VERSION; \
	docker-php-ext-install-pecl-static event $PHP_EXT_EVENT_VERSION; \
	docker-php-ext-install-pecl-static excimer $PHP_EXT_EXCIMER_VERSION; \
	docker-php-ext-install-pecl-static igbinary $PHP_EXT_IGBINARY_VERSION; \
	docker-php-ext-install-pecl-static memcached $PHP_EXT_MEMCACHED_VERSION; \
	docker-php-ext-install-pecl-static protobuf $PHP_EXT_PROTOBUF_VERSION; \
	docker-php-ext-install-pecl-static redis $PHP_EXT_REDIS_VERSION; \
	docker-php-ext-install-pecl-static yaml $PHP_EXT_YAML_VERSION; \
# refresh ./buildconf
	rm -f configure; \
	./buildconf --force; \
	\
	./configure \
		--build="$gnuArch" \
		--with-config-file-path="$PHP_INI_DIR" \
		--with-config-file-scan-dir="$PHP_INI_DIR/conf.d" \
		--with-libdir="lib" \
		--enable-option-checking=fatal \
		\
# https://github.com/docker-library/php/issues/439
		--with-mhash \
# https://github.com/docker-library/php/issues/822
		--with-pic \
# in PHP 7.4+, the pecl/pear installers are officially deprecated (requiring an explicit "--with-pear")
		--with-pear \
# https://github.com/docker-library/php/pull/939#issuecomment-730501748
		--enable-embed \
# disable default sqlite driver \
		--without-sqlite3 \
		--without-pdo-sqlite \
		\
# configure bundled static PHP extensions
		--enable-bcmath \
		--enable-intl \
		--enable-mbstring \
		--enable-mysqlnd \
		--enable-opcache \
		--enable-pcntl \
		--enable-sockets \
		--with-curl \
		--with-iconv \
		--with-openssl \
		--with-password-argon2 \
		--with-readline \
		--with-sodium \
		--with-imap=/usr \
		--with-imap-ssl \
		--with-kerberos \
		--with-mysqli \
		--with-pdo-mysql \
		--with-pgsql \
		--with-pdo-pgsql \
		--with-zip \
		--with-zlib \
# configure third party static PHP extensions
		--enable-ds \
		--enable-igbinary \
		--enable-memcached \
		--enable-memcached-igbinary \
		--enable-memcached-session \
		--enable-protobuf \
		--enable-redis \
		--with-amqp \
		--with-yaml \
# configure third party shared PHP extensions
		--enable-excimer=shared \
		--with-event-core=shared \
		--with-event-extra; \
	\
	make -j "$(nproc)"; \
	find -type f -name '*.a' -delete; \
	make install; \
	find \
		/usr/local \
		-type f \
		-perm '/0111' \
		-exec sh -euxc ' \
			strip --strip-all "$@" || : \
		' -- '{}' + \
	; \
	make clean; \
	\
# https://github.com/docker-library/php/issues/692 (copy default example "php.ini" files somewhere easily discoverable)
	cp -v php.ini-* "$PHP_INI_DIR/"; \
	\
	cd /; \
	docker-php-source delete; \
	\
# update pecl channel definitions https://github.com/docker-library/php/issues/443
	pecl update-channels; \
	pecl install "xdebug-$PHP_EXT_XDEBUG_VERSION"; \
	rm -rf /tmp/pear ~/.pearrc; \
	\
# enable Zend OPcache by default
	docker-php-ext-enable opcache; \
	\
	# smoke test
	php --version

ARG COMPOSER_VERSION="2.6.6"

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version="$COMPOSER_VERSION"

WORKDIR /var/www/html

FROM debian:bookworm-slim AS runtime

ARG PHP_RUNTIME_DEPS="libbrotli1 libevent-2.1-7 libpsl5 libssl3 libzip4 zlib1g libargon2-1 libonig5 libreadline8 libsodium23 libxml2 libicu72 libc-client2007e libkrb5-3 libpcre2-32-0 libpq5 libmemcached11 librabbitmq4 libyaml-0-2"

COPY --from=build /usr/local/lib/ /usr/local/lib/
COPY --from=build /usr/local/etc/php/ /usr/local/etc/php/
COPY --from=build /usr/local/bin/php /usr/local/bin/

RUN set -eux; \
# prevent Debian's PHP packages from being installed \
	{ \
		echo 'Package: php*'; \
		echo 'Pin: release *'; \
		echo 'Pin-Priority: -1'; \
	} > /etc/apt/preferences.d/no-debian-php; \
	\
# install runtime dependencies \
	apt-get update; \
	apt-get install -y --no-install-recommends $PHP_RUNTIME_DEPS; \
	rm -rf /var/lib/apt/lists/*; \
	\
# make workdir
	mkdir -p /var/www/html; \
	chown www-data:www-data /var/www/html; \
	chmod 1700 /var/www/html; \
	\
# smoke test
	php --version

WORKDIR /var/www/html/
USER www-data

CMD ["php", "-a"]
