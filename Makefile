.PHONY: php-cs-fixer
php-cs-fixer:
	./tools/php-cs-fixer/vendor/bin/php-cs-fixer fix \
		--allow-risky=yes \
		--config ./tools/php-cs-fixer/ruleset.php_cs \
		src
	./tools/php-cs-fixer/vendor/bin/php-cs-fixer fix \
		--allow-risky=yes \
		--config ./tools/php-cs-fixer/ruleset.php_cs \
		tests

.PHONY: tests
tests:
	./vendor/bin/phpunit -c phpunit.xml