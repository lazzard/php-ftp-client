# Lazzard/FtpClient

[![Downloads](https://img.shields.io/packagist/dm/lazzard/php-ftp-client)](https://packagist.org/packages/lazzard/php-ftp-client)
[![Packagist Version](https://img.shields.io/packagist/v/lazzard/php-ftp-client?style=flat-square)](https://packagist.org/packages/lazzard/php-ftp-client)
[![Minimum PHP version](https://img.shields.io/packagist/php-v/lazzard/php-ftp-client?color=%238892bf&style=flat-square)](https://packagist.org/packages/lazzard/php-ftp-client)
![License](https://img.shields.io/packagist/l/lazzard/php-ftp-client?color=critical&style=flat-square)

A library that wraps the PHP FTP functions in an OOP way.

*Note: This library aimed to be a full FTP/FTPS client solution for the old (5.5+) and newer PHP releases (7.2+) that support FTP extension.*

## Requirements

 * PHP version >= 5.6.0.
 * FTP extension enabled.

## Installation

The recommended way to install this library is through composer :

```console
composer require lazzard/php-ftp-client
```

or just clone the repo using git :

```bash
git clone https://github.com/lazzard/php-ftp-client
```

then generate the autoload files :

```console
composer dump-autoload
```

## Getting Started

### Usage

Create an FTP connection
```php
$connection = new FtpConnection('host', 'foo', '1234');
$connection->open();
```

Or create a secure FTP connection
```php
$connection = new FtpSSLConnection('host', 'bar', '1234');
$connection->open();
```

Configure the connection

```php
$config = new FtpConfig(ConnectionInterface $connection);
$config->setPassive(true);
```

Start working with the base class `FtpClient`

```php
$client = new FtpClient(ConnectionInterface $connection);
```

#### upload/download

```php
// download a remote file
$client->download('path/to/remote/file', 'path/to/local/file');

// upload a local file to remote server
$client->upload('path/to/local/file', 'path/to/remote/file');

// download a remote file asynchronously
$client->asyncDownload('path/to/remote/file', 'path/to/local/file', function ($state) {
    // do something every second while downloading this file
}, 1, FtpWrapper::BINARY);

// upload a remote file asynchronously
$client->asyncUpload('path/to/local/file', 'path/to/remote/file', function ($state) {
    // do something 
}, 1, FtpWrapper::BINARY);
```

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
$client->getFileContent('path/to/file');

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
You can see all available methods [here](docs/FtpClient.md).

## Full example

```php
try {
    // Connection
    $connection = new FtpConnection("localhost", "foo", "12345");
    $connection->open();
    
    // Configuration
    $config = new FtpConfig($connection);
    $config->setPassive(true);
    
     // FtpClient
    $client = new FtpClient($connection);
    
    // Start working
    print_r($client->getFeatures());
    
    // Close connection
    $connection->close();
} catch (FtpClientException $ex) { // Use FtpClientException to catch this library exceptions
    echo($ex->getMessage());
}
```

## More documentation

 * [Manipulate an FTP connection with **FtpConnectionInterface**][1]
 * [Configure the connection instance with **FtpConfig**][2]
 * [Start working with the base class **FtpClient**][3]
 * [Sending FTP commands with **FtpCommand**][4]
 * [Using the **FtpWrapper** Directly][5]
 * [How i can run test units ?][6]
 
[1]: docs/FtpConnectionInterface.md
[2]: docs/FtpConfig.md
[3]: docs/FtpClient.md
[4]: docs/FtpCommand.md
[5]: docs/FtpWrapper.md
[6]: docs/tests.md

## Version Guidance

| Version    | Status        | Last Release | PHP Version |
|------------|---------------|--------------|-------------|
| 1.0.x      | EOL           | [v1.0.2][7]  | >= 5.5      |
| 1.3.x      | Latest        | [v1.3.5][9]  | >= 5.6      |

[7]: https://github.com/lazzard/php-ftp-client/releases/tag/v1.0.2
[8]: https://github.com/lazzard/php-ftp-client/releases/tag/v1.1.0
[9]: https://github.com/lazzard/php-ftp-client/releases/tag/v1.3.5

## License

MIT License. please see the [LICENSE FILE](LICENSE) for more information. 
