#
# NOTE: THIS DOCKERFILE IS GENERATED VIA "apply-templates.sh"
#
# PLEASE DO NOT EDIT IT DIRECTLY.
#

FROM debian:bookworm-slim

# prevent Debian's PHP packages from being installed
# https://github.com/docker-library/php/pull/542
RUN set -eux; \
	{ \
		echo 'Package: php*'; \
		echo 'Pin: release *'; \
		echo 'Pin-Priority: -1'; \
	} > /etc/apt/preferences.d/no-debian-php

# dependencies required for running "phpize"
# (see persistent deps below)
ARG PHPIZE_DEPS="autoconf dpkg-dev file g++ gcc libc-dev make pkg-config re2c"

# persistent / runtime deps
RUN set -eux; \
	apt-get update; \
	apt-get install -y --no-install-recommends \
		$PHPIZE_DEPS \
		ca-certificates \
		curl \
		xz-utils \
	; \
	rm -rf /var/lib/apt/lists/*

ARG PHP_INI_DIR="/usr/local/etc/php"
RUN set -eux; \
	mkdir -p "$PHP_INI_DIR/conf.d"; \
# allow running as an arbitrary user (https://github.com/docker-library/php/issues/743)
	[ ! -d /var/www/html ]; \
	mkdir -p /var/www/html; \
	chown www-data:www-data /var/www/html; \
	chmod 1777 /var/www/html

# Apply stack smash protection to functions using local buffers and alloca()
# Make PHP's main executable position-independent (improves ASLR security mechanism, and has no performance impact on x86_64)
# Enable optimization (-O2)
# Enable linker optimization (this sorts the hash buckets to improve cache locality, and is non-default)
# https://github.com/docker-library/php/issues/272
# -D_LARGEFILE_SOURCE and -D_FILE_OFFSET_BITS=64 (https://www.php.net/manual/en/intro.filesystem.php)
ARG PHP_CFLAGS="-fstack-protector-strong -fpic -fpie -O2 -D_LARGEFILE_SOURCE -D_FILE_OFFSET_BITS=64"
ARG PHP_CPPFLAGS="$PHP_CFLAGS"
ARG PHP_LDFLAGS="-Wl,-O1 -pie"

ARG GPG_KEYS="1198C0117593497A5EC5C199286AF1F9897469DC C28D937575603EB4ABB725861C0779DC5C0A9DE4 AFD8691FDAEDF03BDF6E460563F15A9B715376CA"

ARG PHP_VERSION="8.3.15"
ARG PHP_URL="https://www.php.net/distributions/php-8.3.15.tar.xz"
ARG PHP_ASC_URL="https://www.php.net/distributions/php-8.3.15.tar.xz.asc"
ARG PHP_SHA256="3df5d45637283f759eef8fc3ce03de829ded3e200c3da278936a684955d2f94f"

RUN set -eux; \
	\
	savedAptMark="$(apt-mark showmanual)"; \
	apt-get update; \
	apt-get install -y --no-install-recommends gnupg; \
	rm -rf /var/lib/apt/lists/*; \
	\
	mkdir -p /usr/src; \
	cd /usr/src; \
	\
	curl -fsSL -o php.tar.xz "$PHP_URL"; \
	\
	if [ -n "$PHP_SHA256" ]; then \
		echo "$PHP_SHA256 *php.tar.xz" | sha256sum -c -; \
	fi; \
	\
	if [ -n "$PHP_ASC_URL" ]; then \
		curl -fsSL -o php.tar.xz.asc "$PHP_ASC_URL"; \
		export GNUPGHOME="$(mktemp -d)"; \
		for key in $GPG_KEYS; do \
			gpg --batch --keyserver keyserver.ubuntu.com --recv-keys "$key"; \
		done; \
		gpg --batch --verify php.tar.xz.asc php.tar.xz; \
		gpgconf --kill all; \
		rm -rf "$GNUPGHOME"; \
	fi; \
	\
	apt-mark auto '.*' > /dev/null; \
	apt-mark manual $savedAptMark > /dev/null; \
	apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false

# Set static PHP pecl extensions version
ARG PHP_EXT_AMQP_VERSION="2.1.2"
ARG PHP_EXT_IGBINARY_VERSION="3.2.16"
ARG PHP_EXT_MEMCACHED_VERSION="3.3.0"
ARG PHP_EXT_PROTOBUF_VERSION="4.28.3"
ARG PHP_EXT_YAML_VERSION="2.2.4"

# Build curl with c-ares resolver
RUN set -eux; \
	apt-get update; \
	apt-get install -y --no-install-recommends \
		libbrotli-dev \
		libpsl-dev \
		libssl-dev \
	; \
	cd /usr/src; \
	curl -o ./c-ares.gz -L https://github.com/c-ares/c-ares/releases/download/v1.33.1/c-ares-1.33.1.tar.gz; \
	tar -xzvf ./c-ares.gz; \
	cd ./c-ares-1.33.1; \
	./configure; \
	make; \
	make install; \
	cd /usr/src; \
	curl -o ./curl.gz -L https://github.com/curl/curl/releases/download/curl-8_10_1/curl-8.10.1.tar.gz; \
	tar -xzvf ./curl.gz; \
	cd ./curl-8.10.1; \
	LDFLAGS="-Wl,-rpath,/usr/local/lib" ./configure --enable-ares=/usr/local/include --with-brotli --with-openssl --with-libpsl --disable-manual --disable-docs; \
	make; \
	make install; \
	ln -sT /usr/local/include/curl /usr/include/curl; \
	rm -rf /usr/src/curl* /usr/src/c-ares*; \
	rm -rf /var/lib/apt/lists/*

COPY docker-php-ext-install-pecl-static /usr/local/bin/
COPY docker-php-source /usr/local/bin/

RUN set -eux; \
	\
	savedAptMark="$(apt-mark showmanual)"; \
	apt-get update; \
	apt-get install -y --no-install-recommends \
		libargon2-dev \
		libcurl4-openssl-dev \
		libonig-dev \
		libreadline-dev \
		libsodium-dev \
		libsqlite3-dev \
		libssl-dev \
		libxml2-dev \
		zlib1g-dev \
		libicu-dev \
		libc-client-dev \
		libkrb5-dev \
		libpcre2-dev \
		libpq-dev \
		libmemcached-dev \
		librabbitmq-dev \
		libyaml-dev \
		libzip-dev \
	; \
	\
	export \
		CFLAGS="$PHP_CFLAGS" \
		CPPFLAGS="$PHP_CPPFLAGS" \
		LDFLAGS="$PHP_LDFLAGS" \
# https://github.com/php/php-src/blob/d6299206dd828382753453befd1b915491b741c6/configure.ac#L1496-L1511
		PHP_BUILD_PROVIDER='https://github.com/docker-library/php' \
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
# Download & install static PHP pecl extensions version before ./configure
	docker-php-ext-install-pecl-static amqp $PHP_EXT_AMQP_VERSION; \
	docker-php-ext-install-pecl-static igbinary $PHP_EXT_IGBINARY_VERSION; \
	docker-php-ext-install-pecl-static memcached $PHP_EXT_MEMCACHED_VERSION; \
	docker-php-ext-install-pecl-static protobuf $PHP_EXT_PROTOBUF_VERSION; \
	docker-php-ext-install-pecl-static yaml $PHP_EXT_YAML_VERSION; \
# Refresh ./buildconf
	rm -f configure; \
	./buildconf --force; \
	\
	./configure \
		--build="$gnuArch" \
		--with-config-file-path="$PHP_INI_DIR" \
		--with-config-file-scan-dir="$PHP_INI_DIR/conf.d" \
		\
# make sure invalid --configure-flags are fatal errors instead of just warnings
		--enable-option-checking=fatal \
		\
# https://github.com/docker-library/php/issues/439
		--with-mhash \
		\
# https://github.com/docker-library/php/issues/822
		--with-pic \
		\
# --enable-mbstring is included here because otherwise there's no way to get pecl to use it properly (see https://github.com/docker-library/php/issues/195)
		--enable-mbstring \
# --enable-mysqlnd is included here because it's harder to compile after the fact than extensions are (since it's a plugin for several extensions, not an extension in itself)
		--enable-mysqlnd \
# https://wiki.php.net/rfc/argon2_password_hash
		--with-password-argon2 \
# https://wiki.php.net/rfc/libsodium
		--with-sodium=shared \
# always build against system sqlite3 (https://github.com/php/php-src/commit/6083a387a81dbbd66d6316a3a12a63f06d5f7109)
		--with-pdo-sqlite=/usr \
		--with-sqlite3=/usr \
		\
		--with-curl \
		--with-iconv \
		--with-openssl \
		--with-readline \
		--with-zlib \
		\
# https://github.com/docker-library/php/pull/1259
		--enable-phpdbg \
		--enable-phpdbg-readline \
		\
# in PHP 7.4+, the pecl/pear installers are officially deprecated (requiring an explicit "--with-pear")
		--with-pear \
		\
		--with-libdir="lib/$debMultiarch" \
		\
# https://github.com/docker-library/php/pull/939#issuecomment-730501748
		--enable-embed \
	; \
	\
# Reconfigure bundled static PHP extensions after ./configure
	./config.nice \
		--with-libdir=lib \
		--disable-phpdbg \
		--disable-mysqlnd \
		--without-sqlite3 \
		--without-pdo-sqlite \
		--enable-bcmath \
		--enable-intl \
		--enable-opcache \
		--enable-pcntl \
		--enable-sockets \
		--with-sodium \
		--with-imap=/usr \
		--with-kerberos \
		--with-imap-ssl \
		--with-pgsql \
		--with-pdo-pgsql \
		--with-zip; \
# Enable third party static PHP extensions after ./configure \
	./config.nice \
		--enable-igbinary \
		--enable-memcached \
		--enable-memcached-session \
		--enable-memcached-igbinary \
		--enable-protobuf \
		--with-amqp \
		--with-yaml; \
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
# reset apt-mark's "manual" list so that "purge --auto-remove" will remove all build dependencies
	apt-mark auto '.*' > /dev/null; \
	[ -z "$savedAptMark" ] || apt-mark manual $savedAptMark; \
	find /usr/local -type f -executable -exec ldd '{}' ';' \
		| awk '/=>/ { so = $(NF-1); if (index(so, "/usr/local/") == 1) { next }; gsub("^/(usr/)?", "", so); printf "*%s\n", so }' \
		| sort -u \
		| xargs -r dpkg-query --search \
		| cut -d: -f1 \
		| sort -u \
		| xargs -r apt-mark manual \
	; \
	apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
	rm -rf /var/lib/apt/lists/*; \
	\
# update pecl channel definitions https://github.com/docker-library/php/issues/443
	pecl update-channels; \
	rm -rf /tmp/pear ~/.pearrc; \
	\
# smoke test
	php --version

COPY docker-php-ext-* docker-php-entrypoint /usr/local/bin/

# sodium was built as a shared module (so that it can be replaced later if so desired), so let's enable it too (https://github.com/docker-library/php/issues/598)
#RUN docker-php-ext-enable sodium

# Enable Zend OPcache by default
RUN docker-php-ext-enable --ini-name 0-ext.ini opcache

ENTRYPOINT ["docker-php-entrypoint"]
CMD ["php", "-a"]
