PHP := php
SRC := o3po/
DOCS := docs/
PHPUNIT := phpunit
ifndef PHPUNITCOMMAND # on travis we set this via the travis.yml to make sure we run with a phpunit version that is compatible with the used php version
PHPUNITCOMMAND := $(shell command -v $(PHPUNIT) 2> /dev/null)
endif
ifndef PHPUNITCOMMAND
PHPUNITCOMMAND := "phpunit"
endif

PHPDOCUMENTORPHAR := phpDocumentor.phar

all:
	@echo "Please specify a target to make:\ndocs:\t\tgenerate the documentation\nlint:\t\trun php in lint mode\ntest:\trun phpunit unit tests\ntest-[test file]:\trun specific unit test"

.PHONY: docs
docs: $(shell find . -type f -name '*.php') $(PHPDOCUMENTORPHAR)
	@$(PHP) $(PHPDOCUMENTORPHAR) -i index.php -i display.php --force --validate --sourcecode -vv -d $(SRC) -t $(DOCS)

$(PHPDOCUMENTORPHAR):
	@wget -O $(PHPDOCUMENTORPHAR) http://www.phpdoc.org/$(PHPDOCUMENTORPHAR)

lint:
	@find . -type f -name '*.php' -exec php -l {} \;

run-tests: test

test: test-.

test-%: $(shell find . -type f -name '*.php') setsttysizenonzero
	$(PHPUNITCOMMAND) --verbose --coverage-clover=coverage.xml --coverage-html=coverage-html --whitelist $(SRC) --bootstrap tests/resources/bootstrap.php --test-suffix 'test.php' tests/$(subst test-,,$@)

setsttysizenonzero:
	@if [ "$(shell stty size)" = "0 0" ]; then stty cols 80; fi
