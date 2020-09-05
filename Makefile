DOCKER_COMPOSE_COMMAND=cd docker && docker-compose -p $(shell basename $(CURDIR))

.PHONY: build
build:
	$(DOCKER_COMPOSE_COMMAND) build app

.PHONY: up
up:
	$(DOCKER_COMPOSE_COMMAND) up

.PHONY: down
down:
	$(DOCKER_COMPOSE_COMMAND) down --remove-orphans

.PHONY: test
test:
	$(DOCKER_COMPOSE_COMMAND) exec app cd tests && phpunit

.PHONY: bash
bash:
	$(DOCKER_COMPOSE_COMMAND) exec app bash

.PHONY: update
update:
	$(DOCKER_COMPOSE_COMMAND) run app composer update

.PHONY: mysql
mysql:
	$(DOCKER_COMPOSE_COMMAND) exec mysql mysql