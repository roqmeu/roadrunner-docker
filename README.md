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

These images are intended to be used as dedicated builder/runtime base images in multi-stage Dockerfiles. The `example/` directory demonstrates this pattern:

- **Builder stage**: use `ghcr.io/roqmeu/rr2025-bookworm-build:latest` (PHP + rr + build libs + PECL + Composer) to install dependencies.
- **Runtime stage**: use `ghcr.io/roqmeu/rr2025-bookworm-runtime:latest` (PHP + rr + runtime libs). Copy only `vendor/` and application sources; no additional install steps are required.

### Typical multi-stage Dockerfile

```Dockerfile
# 1) Builder: PHP + Composer (+ rr in base)
FROM ghcr.io/roqmeu/rr2025-bookworm-build:latest AS cache

COPY composer.json /var/www/html/
RUN composer update -o -a -n --no-cache --no-progress

# 2) Runtime: minimal image with PHP + rr and runtime deps
FROM ghcr.io/roqmeu/rr2025-bookworm-runtime:latest AS app

COPY --chown=www-data:www-data --chmod=700 --from=cache /var/www/html/vendor /var/www/html/vendor
COPY --chown=www-data:www-data --chmod=700 . /var/www/html/
```

### Build process diagram

```mermaid
flowchart TD
  subgraph PHP_Build["php build"]
    B1[Install system deps for building curl and php]
    B2[Build curl]
    B3[Fetch static PECL extensions]
    B4[Build PHP with static extensions]
    B5[Build shared PECL extensions]
    B6[Install Composer]

    direction LR
    B1 --> B2 --> B3 --> B4 --> B5 --> B6
  end

  subgraph RR_Build["roadrunner build"]
    RB1[Copy rr binary]
  end

  subgraph App_Cache["application cache"]
    EC1[OPTIONAL Shared PECL for project]
    EC2[Copy Composer files]
    EC3[Install Composer dependencies]

    direction LR
    EC1 --> EC2 --> EC3
  end

  subgraph PHP_Runtime["php runtime"]
    R1[Copy PHP runtime files]
    R2[Install runtime deps]
    R3[Prepare workdir and permissions]

    direction LR
    R1 --> R2 --> R3
  end

  subgraph RR_Runtime["roadrunner runtime"]
    RR1[Copy rr binary]
    RR2[Entrypoint rr serve]

    direction LR
    RR1 --> RR2
  end

  subgraph App_Runtime["application runtime"]
    EA1[OPTIONAL Copy shared PECL for project]
    EA2[Copy app sources]
    EA3[Copy vendor from cache]
    EA4[OPTIONAL Symfony DI warmup]

    direction LR
    EA1 --> EA2 --> EA3 --> EA4
  end

  PHP_Build --> RR_Build
  RR_Build --> App_Cache
  PHP_Build --> PHP_Runtime
  PHP_Runtime --> RR_Runtime
  RR_Runtime --> App_Runtime
  App_Cache --> App_Runtime
```

### Build and run the example

```bash
docker build -t rr-example --target app ./example
docker run --rm -p 8080:8080 rr-example
# Test: curl http://localhost:8080  ->  Hello RoadRunner!
```

Optional: build base images locally instead of pulling from GHCR:

```bash
make build_php && make build_rr
docker build -t rr-example --target app ./example
```
