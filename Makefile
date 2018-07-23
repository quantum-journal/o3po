PHPUNIT := phpunit-5.0.0.phar

all:
	@echo "Please specify a target to make:\ndocs:\t\tgenerate the documentation\nlint:\t\trun php in lint mode\nrun-tests:\trun phpunit unit tests"

docs: $(shell find . -type f -name '*.php') phpDocumentor.phar
	@php phpDocumentor.phar --force --validate --sourcecode -vv -d . -t docs

phpDocumentor.phar:
	@wget http://www.phpdoc.org/phpDocumentor.phar

lint:
	@find . -type f -name '*.php' -exec php -l {} \;

run-tests: $(shell find . -type f -name '*.php') $(PHPUNIT)
	@php $(PHPUNIT) --bootstrap tests/resources/bootstrap.php --test-suffix '.php' tests/

$(PHPUNIT):
	@wget https://phar.phpunit.de/$(PHPUNIT)
