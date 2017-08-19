# CLI support for translation

[![Latest Version](https://img.shields.io/github/tag/php-translation/cli.svg?style=flat-square)](https://github.com/php-translation/cli/releases)
[![Build Status](https://img.shields.io/travis/php-translation/cli.svg?style=flat-square)](https://travis-ci.org/php-translation/cli)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/php-translation/cli.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-translation/cli)
[![Quality Score](https://img.shields.io/scrutinizer/g/php-translation/cli.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-translation/cli)

CLI support for translation. It allows you use the TranslationBundle without installing it in your application. You can
use the WebUI and sync translations with remote storages.
 
### Use

To use the CLI you need to fetch the binary and add your configuration file. 

```bash
wget https://php-translation.github.io/cli/downloads/translation.phar
chmod +x translation.phar
touch translation.yml
```

Example configuration is the same as for the TranslationBundle. 

```yaml
# translation.yml
translation:
  locales: ["en", "sv"]
  configs:
    app:
      dirs: ["%translation.project_root%/app/Resources/views", "%translation.project_root%/src"]
      output_dir: "%translation.project_root%/app/Resources/translations"
      excluded_names: ["*TestCase.php", "*Test.php"]
      excluded_dirs: [cache, data, logs]
```

> **Note:** "%translation.project_root%" will be your `cwd()` from where you execute the command.

You may now run the same commands as you do with the TranslationBundle but without
the "translation:" prefix

* download                                  
* extract  
* sync  
* etc

``` bash
php translation.phar download
```

You may also run PHP's web server with the WebUI with: 
 
``` bash
php translation.phar webui
```

### Build

To build a phar make sure you have [Box project](https://box-project.github.io/box2/) installed and
`phar.readonly = 0` in your php.ini. 

Read more at: https://moquet.net/blog/distributing-php-cli/

```
make build
```
