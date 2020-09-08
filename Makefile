include .env
export

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

.PHONY: bash
bash:
	$(DOCKER_COMPOSE_COMMAND) exec app bash

.PHONY: test
test:
	$(DOCKER_COMPOSE_COMMAND) run app cd tests && phpunit

.PHONY: update
update:
	$(DOCKER_COMPOSE_COMMAND) run app composer update

.PHONY: clean
clean:
	$(DOCKER_COMPOSE_COMMAND) run app git clean -xdff

.PHONY: mysql
mysql:
	$(DOCKER_COMPOSE_COMMAND) exec mysql mysql
