BOX=~/.composer/vendor/bin/box
PHPSCOPER=./../php-scoper/bin/php-scoper

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
	php bin/console asset:install
	php -d zend.enable_gc=0 -d xdebug.max_nesting_level=500 $(PHPSCOPER) add-prefix --prefix PharTranslation --force
	# Replace all namespaces in configuration files
	find ./build/vendor -type f -name "*.xml" -exec perl -pi -e 's/\.class">([^<]*?)\\/.class">PharTranslation\\\1\\/g' {} \;
	find ./build/vendor -type f -name "*.xml" -exec perl -pi -e 's/ class="([^"]*?)\\/ class="PharTranslation\\\1\\/g' {} \;
	find ./build/vendor -type f -name "*.xml" -exec perl -pi -e 's/ id="([^"]*?)\\/ id="PharTranslation\\\1\\/g' {} \;
	find ./build/vendor -type f -name "*.yml" -exec perl -pi -e 's/class: (.*?)\\/class: PharTranslation\\\1\\/g' {} \;
	# Replace all namespace strings in the Symfony source
	find ./build/vendor/symfony -type f -exec perl -pi -e 's/(?:PharTranslation\\+)?Symfony(\\+)/PharTranslation\1Symfony\1/g' {} \;
	# Handle doctrine annotations
	find ./build/vendor/doctrine/annotations/lib -type f -name "*.php" -exec perl -pi -e "s/'Doctrine(\\\+)Common\\\+Annotations\\\+Annotation(.+?)'/'PharTranslation\1Doctrine\1Common\1Annotations\1Annotation\2'/g" {} \;
	cd build && composer dump-autoload --classmap-authoritative
	#
	#
	cd build && $(BOX) build
	#$(BOX) build
	#mkdir build
	#cp translation.pha* build/
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
