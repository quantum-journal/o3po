PHP := php
SRC := o3po/
DOCS := docs/
PHPUNIT := phpunit
PHPUNITCOMMAND := $(shell command -v $(PHPUNIT) 2> /dev/null)
ifndef PHPUNITCOMMAND
PHPUNITPHAR := $(PHPUNIT)-5.0.0.phar # we download this if no locally installed version could be found, as it is the latest version of phpunit that still works with old php versions
PHPUNITCOMMAND := $(PHP) $(PHPUNITPHAR)
endif

PHPDOCUMENTORPHAR := phpDocumentor.phar

all:
	@echo "Please specify a target to make:\ndocs:\t\tgenerate the documentation\nlint:\t\trun php in lint mode\nrun-tests:\trun phpunit unit tests"

.PHONY: docs
docs: $(shell find . -type f -name '*.php') $(PHPDOCUMENTORPHAR)
	@$(PHP) $(PHPDOCUMENTORPHAR) -i index.php -i display.php --force --validate --sourcecode -vv -d $(SRC) -t $(DOCS)

$(PHPDOCUMENTORPHAR):
	@wget -O $(PHPDOCUMENTORPHAR) http://www.phpdoc.org/$(PHPDOCUMENTORPHAR)

lint:
	@find . -type f -name '*.php' -exec php -l {} \;

run-tests: test

# test: $(shell find . -type f -name '*.php') $(PHPUNITPHAR) setsttysizenonzero
# 	$(PHPUNITCOMMAND) --verbose --coverage-clover=coverage.xml --coverage-html=coverage-html --whitelist $(SRC) --bootstrap tests/resources/bootstrap.php --test-suffix 'test.php' tests/

test: test-.

test-%: $(shell find . -type f -name '*.php') $(PHPUNITPHAR) setsttysizenonzero
	$(PHPUNITCOMMAND) --verbose --coverage-clover=coverage.xml --coverage-html=coverage-html --whitelist $(SRC) --bootstrap tests/resources/bootstrap.php --test-suffix 'test.php' tests/$(subst test-,,$@)



$(PHPUNITPHAR):
	wget -O $(PHPUNITPHAR) https://phar.phpunit.de/$(PHPUNITPHAR)

setsttysizenonzero:
	@if [ "$(shell stty size)" = "0 0" ]; then stty cols 80; fi
