
test:
	test-mocked test-live
test-mocked:
	./apps/bpdts-client/bin/phpunit -c ./apps/bpdts-client/phpunit.xml.dist --exclude-group="realApi"
test-live:
	./apps/bpdts-client/bin/phpunit -c ./apps/bpdts-client/phpunit.xml.dist --exclude-group="default"