 # Container Name
CONTAINER_NAME = agenda-api
DOCKER_EXEC = docker exec -it 
PHP_ARTISAN = php artisan
COMPOSER = composer
DOCKER_COMPOSE = docker-compose

# Initial setup
setup: vendor/autoload.php
	@$(DOCKER_COMPOSE) up -d
	@sleep 30
	@$(DOCKER_EXEC) $(CONTAINER_NAME) $(COMPOSER) install
	@sleep 5
	@$(DOCKER_EXEC) $(CONTAINER_NAME) $(PHP_ARTISAN) key:generate
	@sleep 5
	@$(DOCKER_EXEC) $(CONTAINER_NAME) $(PHP_ARTISAN) migrate
	@sleep 5
	@$(DOCKER_EXEC) $(CONTAINER_NAME) $(PHP_ARTISAN) migrate:fresh --seed

# Start Docker container
up:
	@$(DOCKER_COMPOSE) up -d
	@sleep 3
	@$(DOCKER_EXEC) $(CONTAINER_NAME) $(PHP_ARTISAN) key:generate

# Stop Docker container
down:
	@$(DOCKER_COMPOSE) down

# Remove Docker container
destroy:
	@$(DOCKER_COMPOSE) down -v --remove-orphans

# Tests
test:
	@$(DOCKER_EXEC) $(CONTAINER_NAME) $(PHP_ARTISAN) test

# Clear all cache
clear-cache:
	@$(DOCKER_EXEC) $(CONTAINER_NAME) $(PHP_ARTISAN) cache:clear
	@$(DOCKER_EXEC) $(CONTAINER_NAME) $(PHP_ARTISAN) config:clear
	@$(DOCKER_EXEC) $(CONTAINER_NAME) $(PHP_ARTISAN) route:clear
	@$(DOCKER_EXEC) $(CONTAINER_NAME) $(PHP_ARTISAN) view:clear
