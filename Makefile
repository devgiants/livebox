#!make
include .env
export $(shell sed 's/=.*//' .env)

test-nat-create: up
	docker-compose exec php bin/livebox nat:create --id="test" --ip="192.168.1.10" --external=9999 --internal=22 --whitelist="123.123.123.123" -f /var/www/html/bin/config.yml

test-nat-delete: up
	docker-compose exec php bin/livebox nat:delete --id="test" -f /var/www/html/bin/config.yml

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