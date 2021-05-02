## Running Tests

This library uses PHPUnit for testing.

### Requirements

* phpUnit ^5
* php >= 5.6

### Install

If you don't have PHPUnit installed globally, or you have a different PHPUnit version then run this: 

```console
composer install --dev
```

### Configs

Edit **tests/config.php** with your FTP credentials.

### Run tests

```console
vendor/bin/phpunit
```