FROM ghcr.io/roqmeu/rr2025-bookworm:latest AS rr

COPY composer.json /var/www/html/

RUN composer update

FROM debian:bookworm-slim AS app

COPY --from=rr /usr/local/lib/ /usr/local/lib/
COPY --from=rr /usr/local/etc/php/ /usr/local/etc/php/

COPY --from=rr /usr/local/bin/rr /usr/local/bin/php /usr/local/bin/docker-php-deps-install /usr/local/bin/

RUN docker-php-deps-install runtime && rm -f /usr/local/bin/docker-php-* && php -v && rr -v

COPY --from=rr /var/www/html/ /var/www/html/
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html && chmod -R 1777 /var/www/html

WORKDIR /var/www/html/
USER www-data

ENTRYPOINT ["rr", "serve", "-c", ".rr.yaml"]
