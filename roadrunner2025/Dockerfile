FROM ghcr.io/roadrunner-server/roadrunner:2025.1.2 AS roadrunner

FROM ghcr.io/roqmeu/php8.3-bookworm-rr:8.3.25.1

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr
