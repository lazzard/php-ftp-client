# Lazzard/FtpClient

[![Downloads](https://img.shields.io/packagist/dt/lazzard/php-ftp-client)](https://packagist.org/packages/lazzard/php-ftp-client)
[![Packagist Version](https://img.shields.io/packagist/v/lazzard/php-ftp-client)](https://packagist.org/packages/lazzard/php-ftp-client)
[![Minimum PHP version](https://img.shields.io/packagist/php-v/lazzard/php-ftp-client?color=%238892bf)](https://packagist.org/packages/lazzard/php-ftp-client)
![License](https://img.shields.io/packagist/l/lazzard/php-ftp-client)

This library provides helper classes and methods to manage your FTP files in an OOP way.

*Note: This library aimed to be a full FTP/FTPS client solution for the old **(^5.5)** and newer PHP releases **(^8.0)**
that support FTP extension.*

## Installation

The recommended way to install this library is through composer :

```
composer require lazzard/php-ftp-client
```

## Quick Start

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Lazzard\FtpClient\Connection\FtpSSLConnection;
use Lazzard\FtpClient\Config\FtpConfig;
use Lazzard\FtpClient\FtpClient;
use Lazzard\FtpClient\Exception\FtpClientException;

try {
    $connection = new FtpSSLConnection('host', 'username', 'password');
    $connection->open();

    $config = new FtpConfig($connection);
    $config->setPassive(true);

    $client = new FtpClient($connection);
    
    print_r($client->getFeatures());
    
    $connection->close();
} catch (FtpClientException $ex) {
    print_r($ex->getMessage());
}
```

## Usage

#### upload/download

```php
// download a remote file
$client->download('path/to/remote/file', 'path/to/local/file');

// upload a local file to remote server
$client->upload('path/to/local/file', 'path/to/remote/file');
```

#### Asynchronous upload/download

```php
// download a remote file asynchronously
$client->asyncDownload('path/to/remote/file', 'path/to/local/file', function ($state) {
    // do something every second while downloading this file
}, 1, FtpWrapper::BINARY);

// upload a remote file asynchronously
$client->asyncUpload('path/to/local/file', 'path/to/remote/file', function ($state) {
    // do something 
}, 1, FtpWrapper::BINARY);
```

*Find more about asynchrounous stuff [here](docs/FtpClient.md#asynchronous-transfer-operations).*

#### listing

```php
// get files names within an FTP directory
$client->listDir('path/to/directory');

// get only directories
$client->listDir('path/to/directory', FtpClient::DIR_TYPE);

// get detailed information of each file within a remote directory including 
// the file path of each file
$client->listDirDetails('path/to/directory');

// recursively
$client->listDirDetails('path/to/directory', true);
```

#### copy

```php
// copy a remote file/directory to another directory
$client->copy('path/to/remote/source', 'path/to/remote/directory');

// copy a local file/directory to the server
$client->copyFromLocal('path/to/local/file', 'path/to/remote/directory'); 

// copy a remote file/directory to local machine
$client->copyToLocal('path/to/remote/source', 'path/to/local/directory'); 
```

#### search

```php
// get all png files within the giving directory with their details
$client->find('/.*\.png$/i', 'path/to/directory'); 

// recursively
$client->find('/.*\.png$/i', 'path/to/directory', true); 
```

#### size

```php
// get file size
$client->fileSize('path/to/file');

// get directory size
$client->dirSize('path/to/directory');
```

#### file/directory creating

```php
// create an FTP file
$client->createFile('path/to/file');

// create a file with content
$client->createFile('path/to/file', 'Hello world!!');

// create a remote directory
// note: this method supports recursive directory creation
$client->createDir('directory');
```

#### append

```php
// append the giving content to a remote file
$client->appendFile('path/to/file', $content);
```

#### remove/rename

```php
// remove an FTP file
$client->removeFile('path/to/file');

// remove an FTP directory (be careful all the files within this directory will be removed)
$client->removeDir('path/to/directory');

// rename an FTP file/directory
$client->rename('path/to/file', $newName);
```

#### move

```php
// move an FTP file or directory to another folder
$client->move('path/to/file', 'path/to/directory');
```

#### count

```php
// get the count of all the files within a directory
$client->getCount('path/to/directory');

// recursively
$client->getCount('path/to/directory', true);

// recursively and files only
$client->getCount('path/to/directory', true, FtpClient::FILE_TYPE);
```

#### permissions

```php
// set a permissions on the giving FTP file/directory 
$client->setPermissions('path/to/file', [
    'owner' => 'r-w', // read & write
    'group' => 'w',
    'world' => 'w-r-e'
]);

// or you can use the UNIX file permission digits 
$client->setPermissions('path/to/file', 777);
```

#### is methods

```php
// is an ftp directory ?
$client->isDir('path/to/file/or/directory');

// is a file type ?
$client->isFile('path/to/file/or/directory');

// is an empty file/directory ?
$client->isEmpty('path/to/file/or/directory');

// is exists on the FTP server ?
$client->isExists('path/to/file/or/directory');

// is the server support the size feature ?
$client->isFeatureSupported('SIZE');
```

#### others

```php
// get the last modified time of the giving file (not working with directories)
$client->lastMTime('path/to/file');

// get a content of an FTP file
$client->getFileContent('path/to/file', FtpWrapper::ASCII);

// get all supported features by the FTP server
$client->getFeatures();

// get the server system
$client->getSystem();

// send a request to allocate a space of bytes for the next transfer operation
// some FTP servers requires this before transfer operations 
$client->allocateSpace(2048);

// prevent the server from closing the connection and keeping it alive
$client->keepAlive();
```

*You can see all available methods [here](docs/FtpClient.md).*

## More documentation

* [Manipulate the FTP connection with **ConnectionInterface**.][1]
* [Configure the connection instance with **FtpConfig**.][2]
* [Start working with the base class **FtpClient**.][3]
* [Sending FTP commands with **FtpCommand**.][4]
* [How to use the **FtpWrapper** class directly.][5]
* [Running the integration tests.][6]

[1]: docs/ConnectionInterface.md

[2]: docs/FtpConfig.md

[3]: docs/FtpClient.md

[4]: docs/FtpCommand.md

[5]: docs/FtpWrapper.md

[6]: docs/tests.md

## Version Guidance

| Version    | Status        | Last Release | PHP Version   |
|:----------:|:-------------:|:------------:|:-------------:|
| 1.0.x      | EOL           | [v1.0.2][7]  | >= 5.5        |
| 1.4.x      | EOL           | [v1.4.2][9]  | >= 5.6        |
| 1.5.x      | Latest        | [v1.5.3][9]  | ^7.2 \| 8.0.* |

[7]: https://github.com/lazzard/php-ftp-client/releases/tag/v1.0.2

[8]: https://github.com/lazzard/php-ftp-client/releases/tag/v1.1.0

[9]: https://github.com/lazzard/php-ftp-client/releases/tag/v1.5.3

## Contribution

Feel free to fork this repo if you want to enhance it or adding new features, also reported some issues that may have
you facing while using the library will be very appreciated, Thank you!

## Library supporters

Thanks to JetBrains company for providing tools that really help us to continue maintaining this project.

<img width="150" src="https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.png?_gl=1*1evhn6q*_ga*MzA3MTk5NzQ3LjE2MzU3OTk3MDA.*_ga_V0XZL7QHEB*MTYzNTg5MzE3NS4yLjEuMTYzNTg5MzkzNC4xNg..&_ga=2.162913596.1450626373.1635893177-307199747.1635799700"/>

## License

MIT License. please see the [LICENSE FILE](LICENSE) for more information. 
