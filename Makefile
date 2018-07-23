PHP := php
SRC := o3po/
DOCS := docs/
PHPUNIT := phpunit
PHPUNITCOMMAND := $(shell command -v $(PHPUNIT) 2> /dev/null)
ifndef PHPUNITCOMMAND
PHPUNITPHAR := $(PHPUNIT)-5.0.0.phar
PHPUNITCOMMAND := $(PHP) $(PHPUNITPHAR)
endif

PHPDOCUMENTORPHAR := phpDocumentor.phar

all:
	@echo "Please specify a target to make:\ndocs:\t\tgenerate the documentation\nlint:\t\trun php in lint mode\nrun-tests:\trun phpunit unit tests"

docs: $(shell find . -type f -name '*.php') $(PHPDOCUMENTORPHAR)
	@$(PHP) $(PHPDOCUMENTORPHAR) --force --validate --sourcecode -vv -d $(SRC) -t $(DOCS)

$(PHPDOCUMENTORPHAR):
	@wget -O $(PHPDOCUMENTORPHAR) http://www.phpdoc.org/$(PHPDOCUMENTORPHAR)

lint:
	@find . -type f -name '*.php' -exec php -l {} \;

run-tests: $(shell find . -type f -name '*.php') $(PHPUNITPHAR)
	@$(PHPUNITCOMMAND) --coverage-clover=coverage.xml --whitelist $(SRC) --bootstrap tests/resources/bootstrap.php --test-suffix 'test.php' tests/

$(PHPUNITPHAR):
	@wget -O $(PHPUNITPHAR) https://phar.phpunit.de/$(PHPUNITPHAR)
