
up:
	docker-compose build && docker-compose up -d
down:
	docker-compose stop
rebuild:
	docker-compose down && docker-compose up --build --force-recreate --no-deps -d
test:
	test-mocked test-live
test-mocked:
	./apps/bpdts-client/bin/phpunit -c ./apps/bpdts-client/phpunit.xml.dist --exclude-group="realApi"
test-live:
	./apps/bpdts-client/bin/phpunit -c ./apps/bpdts-client/phpunit.xml.dist --exclude-group="default"