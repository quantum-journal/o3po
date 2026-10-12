PHP := php
SRC := o3po/
DOCS := docs/
DOCS_STAGING := docs.tmp/
PHPUNIT := phpunit
ifndef PHPUNITCOMMAND # on travis we set this via the travis.yml to make sure we run with a phpunit version that is compatible with the used php version
PHPUNITCOMMAND := $(shell command -v $(PHPUNIT) 2> /dev/null)
endif
ifndef PHPUNITCOMMAND
PHPUNITCOMMAND := "phpunit"
endif

PHPDOCUMENTORPHAR := phpDocumentor.phar
PHPDOCUMENTOR_VERSION := 3.10.0
PHPDOCUMENTOR_SHA256 := fe1e7c23ba3329aa6f19ac3c807446159a431a195ec5d9163b0c281a15105207

all:
	@echo "Please specify a target to make:\ndocs:\t\tgenerate the documentation\nlint:\t\trun php in lint mode\ntest:\trun phpunit unit tests\ntest-[test file]:\trun specific unit test"

.PHONY: docs
docs: $(shell find . -type f -name '*.php') $(PHPDOCUMENTORPHAR)
	@rm -rf $(DOCS_STAGING)
	@$(PHP) $(PHPDOCUMENTORPHAR) run -i index.php -i display.php --force --validate --sourcecode -vv -d $(SRC) -t $(DOCS_STAGING)
	@rm -rf $(DOCS)
	@mv $(DOCS_STAGING) $(DOCS)

$(PHPDOCUMENTORPHAR):
	@wget -O $@ https://github.com/phpDocumentor/phpDocumentor/releases/download/v$(PHPDOCUMENTOR_VERSION)/$(PHPDOCUMENTORPHAR) && echo "$(PHPDOCUMENTOR_SHA256)  $@" | sha256sum --check --status || { rm -f $@; exit 1; }

lint:
	@find . -type f -name '*.php' -exec php -l {} \;

run-tests: test

test: test-.

test-%: $(shell find . -type f -name '*.php') setsttysizenonzero
	@if [ "$(shell $(PHPUNITCOMMAND) --version | head -n 1 | sed -n 's/[^0-9]*\([0-9]*\)\..*/\1/p')" -ge "10" ]; then\
		$(PHPUNITCOMMAND) --coverage-clover=coverage.xml --display-deprecations --display-warnings --display-errors --display-phpunit-deprecations --coverage-filter o3po --coverage-html=coverage-html --bootstrap tests/resources/bootstrap.php --test-suffix 'est.php' tests/$(subst test-,,$@);\
	else\
		$(PHPUNITCOMMAND) --verbose --coverage-clover=coverage.xml --coverage-html=coverage-html --whitelist $(SRC) --bootstrap tests/resources/bootstrap.php --test-suffix 'est.php' tests/$(subst test-,,$@);\
	fi


setsttysizenonzero:
	@if [ "$(shell stty size)" = "0 0" ]; then stty cols 80; fi
