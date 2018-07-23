all:
	@echo "Please specify a target to make:\ndocs: generate the documentation\nlint: run php in lint mode"

docs: $(shell find . -type f -name '*.php') phpDocumentor.phar
	@php phpDocumentor.phar --force --validate --sourcecode -vv -d . -t docs

phpDocumentor.phar:
	@wget http://www.phpdoc.org/phpDocumentor.phar

lint:
	@find . -type f -name '*.php' -exec php -l {} \;
