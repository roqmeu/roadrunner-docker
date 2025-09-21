
.PHONY: build_php deploy_php build_rr deploy_rr

build_php:
	set -a; . ./php8.3/conf; set +a; \
	docker build \
		--label "org.opencontainers.image.source=https://github.com/roqmeu/roadrunner-docker" \
		--label "org.opencontainers.image.description=PHP 8.3 for RoadRunner image" \
		-t "ghcr.io/roqmeu/$$IMAGE:$$TAG" \
		-t "ghcr.io/roqmeu/$$IMAGE:latest" \
	./php8.3

deploy_php:
	set -a; . ./php8.3/conf; set +a; \
	docker buildx build \
		--sbom=false \
		--provenance=false \
		--label "org.opencontainers.image.source=https://github.com/roqmeu/roadrunner-docker" \
		--label "org.opencontainers.image.description=PHP 8.3 for RoadRunner image" \
		-t "ghcr.io/roqmeu/$$IMAGE:$$TAG" \
		-t "ghcr.io/roqmeu/$$IMAGE:latest" \
		--platform "$$PLATFORMS" \
		--push \
	./php8.3

build_rr:
	set -a; . ./roadrunner2025/conf; set +a; \
	docker build \
		--label "org.opencontainers.image.source=https://github.com/roqmeu/roadrunner-docker" \
		--label "org.opencontainers.image.description=RoadRunner 2025 with PHP image" \
		-t "ghcr.io/roqmeu/$$IMAGE:$$TAG" \
		-t "ghcr.io/roqmeu/$$IMAGE:latest" \
	./roadrunner2025

deploy_rr:
	set -a; . ./roadrunner2025/conf; set +a; \
	docker buildx build \
		--sbom=false \
		--provenance=false \
		--label "org.opencontainers.image.source=https://github.com/roqmeu/roadrunner-docker" \
		--label "org.opencontainers.image.description=RoadRunner 2025 with PHP image" \
		-t "ghcr.io/roqmeu/$$IMAGE:$$TAG" \
		-t "ghcr.io/roqmeu/$$IMAGE:latest" \
		--platform "$$PLATFORMS" \
		--push \
	./roadrunner2025
