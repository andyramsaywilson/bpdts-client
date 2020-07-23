
up:
	docker-compose build && docker-compose up -d
down:
	docker-compose stop
rebuild:
	docker-compose down && docker-compose up --build --force-recreate --no-deps -d

test: test-mocked test-live
test-mocked:
	docker exec -it -u root technicaltest_php_1 ./vendor/bin/simple-phpunit -c ./phpunit.xml.dist --exclude-group="realApi"
test-live:
	docker exec -it -u root technicaltest_php_1 ./vendor/bin/simple-phpunit -c ./phpunit.xml.dist --exclude-group="default"