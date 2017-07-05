BOX=~/.composer/vendor/bin/box
PHPSCOPER=~/.composer/vendor/bin/php-scoper

.DEFAULT_GOAL := help
.PHONY: build test tu tc e2e tb


help:
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'


##
## Build
##---------------------------------------------------------------------------

build:            ## Build the PHAR
build: vendor
	# Cleanup existing artefacts
	rm -rf build
	# Delete local `composer.lock` to ensure fresh dependencies are used
	rm composer.lock
	#
	# As of now, files included in `autoload-dev` are not excluded from the
	# classmap.
	#
	# See: https://github.com/composer/composer/issues/6457
	#
	# As a result, the the flag `--no-dev` for `composer install` cannot
	# be used and `box.json.dist` must include the `tests` directory
	#
	composer install --prefer-dist
	php -d zend.enable_gc=0 -d xdebug.max_nesting_level=500 $(PHPSCOPER) add-prefix --force
	cd build && composer dump-autoload --classmap-authoritative
	#
	#
	# Warming up cache
	cd build && ./bin/console cache:clear  --no-warmup --no-debug --env=dev
	cd build && ./bin/console cache:warmup --no-debug --env=prod
	cd build && $(BOX) build
	# Install back all the dependencies
	composer install


##
## Rules from files
##---------------------------------------------------------------------------

vendor: composer.lock
	composer install

composer.lock: composer.json
	@echo compose.lock is not up to date.

scoper: build
