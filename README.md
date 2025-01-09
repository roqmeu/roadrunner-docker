# RoadRunner docker

Basic images for PHP projects using RoadRunner.

Based on official cli version of [php](https://hub.docker.com/_/php) and [roadrunner](https://docs.roadrunner.dev/docs/app-server/docker).

Differences between the php image and the official image:
- Disabled phpdbg;
- MySQL and SQLite support is disabled;
- Added PostgreSQL and PDO PostgreSQL support;
- Added fresh Curl with [async DNS resolver](https://curl.se/mail/archive-2019-07/0003.html);
- Added the following extensions when compiling PHP (statically):
  - igbinary - [roadrunner](https://docs.roadrunner.dev/docs/key-value/overview-kv#igbinary-value-serialization), [pecl](https://pecl.php.net/package/igbinary)
  - protobuf - [roadrunner](https://docs.roadrunner.dev/docs/key-value/overview-kv#php-client), [pecl](https://pecl.php.net/package/protobuf)
  - sodium - [roadrunner](https://docs.roadrunner.dev/docs/key-value/overview-kv#end-to-end-value-encryption)
  - amqp - [pecl](https://pecl.php.net/package/amqp)
  - memcached - [pecl](https://pecl.php.net/package/memcached)
  - yaml - [pecl](https://pecl.php.net/package/yaml)
  - sockets

Full list of modules:
```
[PHP Modules]
amqp
bcmath
Core
ctype
curl
date
dom
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
openssl
pcntl
pcre
PDO
pdo_pgsql
pgsql
Phar
posix
protobuf
random
readline
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
