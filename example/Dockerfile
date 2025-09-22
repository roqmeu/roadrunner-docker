FROM ghcr.io/roqmeu/rr2025-bookworm-build:latest AS cache

COPY composer.json /var/www/html/

RUN composer update -o -a -n --no-cache --no-progress

FROM ghcr.io/roqmeu/rr2025-bookworm-runtime:latest AS app

COPY --chown=www-data:www-data --chmod=700 --from=cache /var/www/html/vendor /var/www/html/vendor
COPY --chown=www-data:www-data --chmod=700 . /var/www/html/
