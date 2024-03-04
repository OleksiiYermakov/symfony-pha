install:
	make up
	make copy-env-file
	make composer-install

up:
	docker-compose up -d

down:
	docker-compose down

build:
	docker-compose build

restart:
	make down && make build && make up

composer-install:
	@$(EXEC) docker-compose exec fpm composer install

copy-env-file:
	@$(EXEC) docker-compose exec fpm cp ./.env.example .env

calculate-via-docker: ## Command execution through a docker container
	@$(EXEC) docker-compose exec fpm php ./bin/console app:calculate-commission ./input.csv

calculate-via-symfony: ## Command execution through a Symfony console
	symfony console app:calculate-commission ./input.csv

calculate-via-php: ## Command execution through locally installed php
	php ./bin/console app:calculate-commission ./input.csv

phpunit:
	@$(EXEC) docker-compose exec fpm php ./bin/phpunit --testdox --stop-on-failure

xdebug-enable:
	@$(EXEC) docker-compose exec fpm cp /docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
	docker-compose stop fpm
	docker-compose up -d fpm

xdebug-disable:
	@$(EXEC) docker-compose exec fpm rm /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
	docker-compose stop fpm
	docker-compose up -d fpm

create-input-csv-file:
	@echo "2014-12-31,4,private,withdraw,1200.00,EUR" > ./input.csv
	@echo "2015-01-01,4,private,withdraw,1000.00,EUR" >> ./input.csv
	@echo "2016-01-05,4,private,withdraw,1000.00,EUR" >> ./input.csv
	@echo "2016-01-05,1,private,deposit,200.00,EUR" >> ./input.csv
	@echo "2016-01-06,2,business,withdraw,300.00,EUR" >> ./input.csv
	@echo "2016-01-06,1,private,withdraw,30000,JPY" >> ./input.csv
	@echo "2016-01-07,1,private,withdraw,1000.00,EUR" >> ./input.csv
	@echo "2016-01-07,1,private,withdraw,100.00,USD" >> ./input.csv
	@echo "2016-01-10,1,private,withdraw,100.00,EUR" >> ./input.csv
	@echo "2016-01-10,2,business,deposit,10000.00,EUR" >> ./input.csv
	@echo "2016-01-10,3,private,withdraw,1000.00,EUR" >> ./input.csv
	@echo "2016-02-15,1,private,withdraw,300.00,EUR" >> ./input.csv
	@echo "2016-02-19,5,private,withdraw,3000000,JPY" >> ./input.csv
