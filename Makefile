.PHONY: test phpunit phpstan phpcs
test: phpunit phpstan phpcs

phpunit:
	vendor/bin/phpunit

phpstan:
	vendor/bin/phpstan analyse -c phpstan.neon -l4 src/

phpcs:
	vendor/bin/phpcs src/ tests/
