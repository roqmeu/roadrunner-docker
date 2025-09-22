FROM ghcr.io/roadrunner-server/roadrunner:2025.1.2 AS roadrunner

FROM ghcr.io/roqmeu/php8.3-bookworm-rr-build:8.3.25.1 AS build

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr

FROM ghcr.io/roqmeu/php8.3-bookworm-rr-runtime:8.3.25.1 AS runtime

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr

ENTRYPOINT ["rr", "serve", "-c", ".rr.yaml"]
