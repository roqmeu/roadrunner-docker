# RoadRunner docker

Basic images for PHP projects using RoadRunner.

Based on official cli version of [php](https://hub.docker.com/_/php) and [roadrunner](https://docs.roadrunner.dev/docs/app-server/docker).

<details><summary>Differences between the php image and the official image:</summary>

- Disabled phpdbg;
- Disabled SQLite support;
- Added MySQL and PDO MySQL support;
- Added PostgreSQL and PDO PostgreSQL support;
- Added Composer;
- Added fresh Curl with [async DNS resolver](https://curl.se/mail/archive-2019-07/0003.html);
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

</details>

<details><summary>Full list of modules:</summary>

```
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
