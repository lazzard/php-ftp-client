# Running integration tests

## Requirements

* phpUnit ^9
* php >= 7.4

## Install

If you don't have PHPUnit installed globally, or you have a different PHPUnit version then run this : 

```console
composer install --dev
```

## FTP settings configs

Edit the **tests/config.php** file with your own FTP settings.

## Run the tests

```console
vendor/bin/phpunit
```
