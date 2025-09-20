FROM ghcr.io/roadrunner-server/roadrunner:2025.1.2 AS roadrunner

FROM ghcr.io/roqmeu/roadrunner-docker:php-8.3.25

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr
