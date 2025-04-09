.PHONY: up down build install ssh test quality db-reset cache-clear

# Docker commands
up: ## Start the docker hub
	docker-compose up -d --wait

down: ## Stop the docker hub
	docker-compose down --remove-orphans

build: ## Rebuilds all the containers
	docker-compose build

ssh: ## Connect to the PHP FPM container
	docker-compose exec php bash

test: ## Run tests
	docker-compose exec php bin/phpunit

quality: ## Run code quality tools
	docker-compose exec php vendor/bin/phpstan analyse -l 5 src tests
	docker-compose exec php vendor/bin/php-cs-fixer fix src --dry-run --diff

db-reset: ## Reset the database and load fixtures
	docker-compose exec php symfony console doctrine:database:drop --force --if-exists
	docker-compose exec php symfony console doctrine:database:create
	docker-compose exec php symfony console doctrine:migrations:migrate -n
	docker-compose exec php symfony console doctrine:fixtures:load -n

cache-clear: ## Clear the cache
	docker-compose exec php symfony console cache:clear

logs: ## Show logs
	docker-compose logs -f

help: ## Display this help message
	@cat $(MAKEFILE_LIST) | grep -e "^[a-zA-Z_\-]*: *.*## *" | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
