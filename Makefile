

.PHONY: dev-up
dev-up:
	docker compose --env-file .env.local up

.PHONY: dev-rebuild
dev-rebuild:
	docker compose --env-file .env.local build --no-cache

.PHONY: tailwind-build
tailwind-build:
	docker exec -it boundary_waters_entry_point_alerter-php-1 php bin/console tailwind:build --watch