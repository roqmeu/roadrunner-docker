# RoadRunner docker

Basic images for PHP projects using RoadRunner.

Based on official cli version of [php](https://hub.docker.com/_/php) and [roadrunner](https://docs.roadrunner.dev/docs/app-server/docker).

Differences between the php image and the official image:

- Disabled phpdbg;
- Disabled SQLite support;
- Added MySQL and PDO MySQL support;
- Added PostgreSQL and PDO PostgreSQL support;
- Added Composer;
- Added fresh cURL with [async DNS resolver](https://curl.se/mail/archive-2019-07/0003.html);
- Added the following extensions when compiling PHP (statically):
  - ds - [Data Structures](https://www.php.net/manual/en/book.ds.php), [pecl](https://pecl.php.net/package/ds)
  - igbinary - [roadrunner](https://docs.roadrunner.dev/docs/key-value/overview-kv#igbinary-value-serialization), [pecl](https://pecl.php.net/package/igbinary)
  - protobuf - [roadrunner](https://docs.roadrunner.dev/docs/key-value/overview-kv#php-client), [pecl](https://pecl.php.net/package/protobuf)
  - sodium - [roadrunner](https://docs.roadrunner.dev/docs/key-value/overview-kv#end-to-end-value-encryption)
  - amqp - [pecl](https://pecl.php.net/package/amqp)
  - memcached - [pecl](https://pecl.php.net/package/memcached)
  - redis - [pecl](https://pecl.php.net/package/redis)
  - yaml - [pecl](https://pecl.php.net/package/yaml)
- Added the following extensions (dynamically) and can be enabled:
  - event - [pecl](https://pecl.php.net/package/event)
  - excimer - [pecl](https://pecl.php.net/package/excimer)
  - xdebug - [pecl](https://pecl.php.net/package/yaml)

<details>
<summary>Full list of modules:</summary>

```text
[PHP Modules]
amqp
bcmath
Core
ctype
curl
date
dom
ds
fileinfo
filter
hash
iconv
igbinary
imap
intl
json
libxml
mbstring
memcached
mysqli
mysqlnd
openssl
pcntl
pcre
PDO
pdo_mysql
pdo_pgsql
pgsql
Phar
posix
protobuf
random
readline
redis
Reflection
session
SimpleXML
sockets
sodium
SPL
standard
tokenizer
xml
xmlreader
xmlwriter
yaml
Zend OPcache
zip
zlib

[Zend Modules]
Zend OPcache
```

</details>

## Usage

These images are intended to be used as builder stages in multi-stage Dockerfiles. The `example/` directory demonstrates this pattern:

- **Builder stage**: use `ghcr.io/roqmeu/rr2025-bookworm:latest` (RoadRunner + PHP + Composer) to resolve dependencies and provide PHP/rr binaries and config.
- **Runtime stage**: use `debian:bookworm-slim`, copy only the necessary PHP runtime, config and binaries from the builder, install system runtime libs via `docker-php-deps-install runtime`, then run RoadRunner.

### Typical multi-stage Dockerfile

```Dockerfile
# 1) Builder: PHP + Composer + RoadRunner binaries
FROM ghcr.io/roqmeu/rr2025-bookworm:latest AS rr

COPY composer.json /var/www/html/
RUN composer update

# 2) Runtime: slim Debian with only runtime deps
FROM debian:bookworm-slim AS app

# Copy PHP runtime and config
COPY --from=rr /usr/local/lib/ /usr/local/lib/
COPY --from=rr /usr/local/etc/php/ /usr/local/etc/php/

# Copy binaries and helper
COPY --from=rr /usr/local/bin/rr /usr/local/bin/php /usr/local/bin/docker-php-deps-install /usr/local/bin/

# Install system runtime libraries required by PHP and check binaries
RUN docker-php-deps-install runtime && rm -f /usr/local/bin/docker-php-* && php -v && rr -v

# Copy vendor and app sources
COPY --from=rr /var/www/html/ /var/www/html/
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html && chmod -R 1777 /var/www/html

WORKDIR /var/www/html/
USER www-data
ENTRYPOINT ["rr", "serve", "-c", ".rr.yaml"]
```

### Build and run the example

```bash
docker build -t rr-example ./example
docker run --rm -p 8080:8080 rr-example
# Test: curl http://localhost:8080  ->  Hello RoadRunner!
```

Optional: build base images locally instead of pulling from GHCR:

```bash
make build_php && make build_rr
docker build -t rr-example ./example
```
