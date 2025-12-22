.PHONY: help up down restart build logs shell composer artisan test migrate fresh seed install dev prod clean cache-clear

# Default target
help:
	@echo "Available commands:"
	@echo "  make up              - Start development containers"
	@echo "  make down            - Stop containers"
	@echo "  make restart         - Restart containers"
	@echo "  make build           - Build containers"
	@echo "  make logs            - View container logs"
	@echo "  make shell           - Access PHP container shell"
	@echo "  make composer        - Run composer install"
	@echo "  make artisan CMD=... - Run artisan command"
	@echo "  make test            - Run PHPUnit tests"
	@echo "  make migrate         - Run migrations"
	@echo "  make fresh           - Fresh migrate with seed"
	@echo "  make seed            - Run database seeders"
	@echo "  make install         - Install dependencies"
	@echo "  make dev             - Start development environment"
	@echo "  make prod            - Start production environment"
	@echo "  make clean           - Clean up containers and volumes"
	@echo "  make cache-clear     - Clear Laravel cache"

# Development environment
up:
	docker-compose -f docker-compose.local.yaml up -d

down:
	docker-compose -f docker-compose.local.yaml down

restart: down up

build:
	docker-compose -f docker-compose.local.yaml build

logs:
	docker-compose -f docker-compose.local.yaml logs -f

# Production environment
prod-up:
	docker-compose -f docker-compose.prod.yaml up -d

prod-down:
	docker-compose -f docker-compose.prod.yaml down

prod-build:
	docker-compose -f docker-compose.prod.yaml build

# Container access
shell:
	docker-compose -f docker-compose.local.yaml exec sms-api bash

# Composer
composer:
	docker-compose -f docker-compose.local.yaml exec sms-api composer install

composer-update:
	docker-compose -f docker-compose.local.yaml exec sms-api composer update

# Artisan commands
artisan:
	docker-compose -f docker-compose.local.yaml exec sms-api php artisan $(CMD)

migrate:
	docker-compose -f docker-compose.local.yaml exec sms-api php artisan migrate

fresh:
	docker-compose -f docker-compose.local.yaml exec sms-api php artisan migrate:fresh --seed

seed:
	docker-compose -f docker-compose.local.yaml exec sms-api php artisan db:seed

rollback:
	docker-compose -f docker-compose.local.yaml exec sms-api php artisan migrate:rollback

# Testing
test:
	docker-compose -f docker-compose.local.yaml exec sms-api php artisan test

test-coverage:
	docker-compose -f docker-compose.local.yaml exec sms-api php artisan test --coverage

# Cache management
cache-clear:
	docker-compose -f docker-compose.local.yaml exec sms-api php artisan cache:clear
	docker-compose -f docker-compose.local.yaml exec sms-api php artisan config:clear
	docker-compose -f docker-compose.local.yaml exec sms-api php artisan route:clear
	docker-compose -f docker-compose.local.yaml exec sms-api php artisan view:clear

cache-optimize:
	docker-compose -f docker-compose.local.yaml exec sms-api php artisan config:cache
	docker-compose -f docker-compose.local.yaml exec sms-api php artisan route:cache
	docker-compose -f docker-compose.local.yaml exec sms-api php artisan view:cache

# Installation
install: build up composer migrate

dev: up logs

# Cleanup
clean:
	docker-compose -f docker-compose.local.yaml down -v
	docker system prune -f

# Database backup
db-backup:
	docker-compose -f docker-compose.local.yaml exec db mysqldump -u root -p sms > backup_$(shell date +%Y%m%d_%H%M%S).sql
