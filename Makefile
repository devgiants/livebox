#!make
include .env
export $(shell sed 's/=.*//' .env)

composer-install: up
	docker-compose exec php composer install

bash-php: up
	docker-compose exec php bash

up:
	docker-compose up -d --build

down:
	docker-compose down

build:
	docker-compose build