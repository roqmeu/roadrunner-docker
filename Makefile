.PHONY: build_php deploy_php build_rr deploy_rr

build_php:
	set -a; . ./php8.3/conf; set +a; \
	docker build \
		-t "ghcr.io/roqmeu/$$IMAGE_BUILD:$$TAG" \
		-t "ghcr.io/roqmeu/$$IMAGE_BUILD:latest" \
		--target=build \
	./php8.3
	set -a; . ./php8.3/conf; set +a; \
	docker build \
		-t "ghcr.io/roqmeu/$$IMAGE_RUNTIME:$$TAG" \
		-t "ghcr.io/roqmeu/$$IMAGE_RUNTIME:latest" \
		--target=runtime \
	./php8.3

deploy_php:
	set -a; . ./php8.3/conf; set +a; \
	docker buildx build \
		--sbom=false \
		--provenance=false \
		--cache-from="ghcr.io/roqmeu/$$IMAGE_BUILD:latest" \
		--label "org.opencontainers.image.source=https://github.com/roqmeu/roadrunner-docker" \
		--label "org.opencontainers.image.description=PHP 8.3 for RoadRunner 'BUILD' stage image" \
		-t "ghcr.io/roqmeu/$$IMAGE_BUILD:$$TAG" \
		-t "ghcr.io/roqmeu/$$IMAGE_BUILD:latest" \
		--platform "$$PLATFORMS" \
		--target=build \
		--push \
	./php8.3
	set -a; . ./php8.3/conf; set +a; \
	docker buildx build \
		--sbom=false \
		--provenance=false \
		--cache-from="ghcr.io/roqmeu/$$IMAGE_RUNTIME:latest" \
		--label "org.opencontainers.image.source=https://github.com/roqmeu/roadrunner-docker" \
		--label "org.opencontainers.image.description=PHP 8.3 for RoadRunner 'RUNTIME' stage image" \
		-t "ghcr.io/roqmeu/$$IMAGE_RUNTIME:$$TAG" \
		-t "ghcr.io/roqmeu/$$IMAGE_RUNTIME:latest" \
		--platform "$$PLATFORMS" \
		--target=runtime \
		--push \
	./php8.3

build_rr:
	set -a; . ./roadrunner2025/conf; set +a; \
	docker build \
		-t "ghcr.io/roqmeu/$$IMAGE_BUILD:$$TAG" \
		-t "ghcr.io/roqmeu/$$IMAGE_BUILD:latest" \
		--target=build \
	./roadrunner2025
	set -a; . ./roadrunner2025/conf; set +a; \
	docker build \
		-t "ghcr.io/roqmeu/$$IMAGE_RUNTIME:$$TAG" \
		-t "ghcr.io/roqmeu/$$IMAGE_RUNTIME:latest" \
		--target=runtime \
	./roadrunner2025

deploy_rr:
	set -a; . ./roadrunner2025/conf; set +a; \
	docker buildx build \
		--sbom=false \
		--provenance=false \
		--cache-from="ghcr.io/roqmeu/$$IMAGE_BUILD:latest" \
		--label "org.opencontainers.image.source=https://github.com/roqmeu/roadrunner-docker" \
		--label "org.opencontainers.image.description=RoadRunner 2025 with PHP 'BUILD' stage image" \
		-t "ghcr.io/roqmeu/$$IMAGE_BUILD:$$TAG" \
		-t "ghcr.io/roqmeu/$$IMAGE_BUILD:latest" \
		--platform "$$PLATFORMS" \
		--target=build \
		--push \
	./roadrunner2025
	set -a; . ./roadrunner2025/conf; set +a; \
	docker buildx build \
		--sbom=false \
		--provenance=false \
		--cache-from="ghcr.io/roqmeu/$$IMAGE_RUNTIME:latest" \
		--label "org.opencontainers.image.source=https://github.com/roqmeu/roadrunner-docker" \
		--label "org.opencontainers.image.description=RoadRunner 2025 with PHP 'RUNTIME' stage image" \
		-t "ghcr.io/roqmeu/$$IMAGE_RUNTIME:$$TAG" \
		-t "ghcr.io/roqmeu/$$IMAGE_RUNTIME:latest" \
		--platform "$$PLATFORMS" \
		--target=runtime \
		--push \
	./roadrunner2025
