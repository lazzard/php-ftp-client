# Lazzard/FtpClient

[![Stable Version](https://img.shields.io/packagist/v/lazzard/php-ftp-client?color=success&label=stable)](https://packagist.org/packages/lazzard/php-ftp-client)
[![Minimum PHP version](https://img.shields.io/packagist/php-v/lazzard/php-ftp-client)](https://packagist.org/packages/lazzard/php-ftp-client)
![Tested on](https://img.shields.io/badge/tested-5.6.4-lightgray)
[![Downloads](https://img.shields.io/packagist/dt/lazzard/php-ftp-client?color=blueviolet&style=social)](https://packagist.org/packages/lazzard/php-ftp-client)

A library that wraps the PHP FTP functions in an OOP way.

**Note: This library aimed to be a full FTP/FTPS client solution for the old (5.5+) and newer PHP releases (7.2+) that support FTP extension.**

## Requirements

 * PHP version >= 5.6.0.
 * FTP extension enabled.

## Installation

The recommended way to install this library is through composer:

```console
composer require lazzard/php-ftp-client
```

or just clone the repo using git:

```bash
git clone https://github.com/lazzard/php-ftp-client
```

## Getting Started

### Usage

Create an FTP connection
```php
$connection = new FtpConnection('host', 'foo', '1234');
$connection->open();
```

Create a secure FTP connection
```php
$connection = new FtpSSLConnection('host', 'bar', '1234');
$connection->open();
```

Configure the connection
```php
$config = new FtpConfig($connection);
$config->setPassive(true);
```

Start working with `FtpClient`
```php
$client = new FtpClient($connection);
```

#### upload/download

```php
// download a remote file
$client->download('public_html/commands.xlsx', 'commands.xlsx');

// upload a local file to remote server
$client->upload('assets/image.png', 'public_html/images/image.png');

// download a remote file asynchronously
$client->asyncDownload('illustrations/assets.zip', 'assets.zip', function ($state) {
    // do something every second while downloading this file
});

// upload a remote file asynchronously
$client->asyncUpload('wallpapers.zip', 'public_html', function ($state) {
    // do something
});
```

#### listing

```php
// get files names within an FTP directory
$client->listDirectory('public_html');

// get only directories
$client->listDirectory('public_html', FtpClient::DIR_TYPE);

// get detailed information of each file within an FTP directory including the file path
$client->listDirectoryDetails('public_html');

// recursively
$client->listDirectoryDetails('public_html', true);
```

#### remove/rename

```php
// remove an FTP file
$client->removeFile($remoteFile);

// remove a directory (this will remove all the file within the directory)
$client->removeDirectory($directory);

// rename an FTP file/directory
$client->rename($remoteFile, $newName);
```

#### copy file/directory from local
```php
// copy a local directory to the giving path in the server
$client->copyFromLocal('media/images', 'htdocs'); 
```

#### size

```php
// get file size
$client->fileSize('public_html/presentation.docx');

// get directory size
$client->dirSize('public_html/assets');
```

#### file/directory creating
 
```php
// create an FTP file
$client->createFile('public_html/example.txt');

// create a file with content
$client->createFile('public_html/example.txt', 'Hello world!!');

// get directory size
$client->createDirectory('public_html/assets');
```

#### move

```php
// move an FTP file or directory to another folder
$client->move($remoteFile, $destinationFolder);
```

#### count

```php
// get the count of all the files within a directory
$client->getCount($directory);

// recursively
$client->getCount($directory, true);

// recursively and files only
$client->getCount($directory, true, FtpClient::FILE_TYPE);
```

#### permissions 

```php
// set a permissions on the giving FTP file/directory 
$client->setPermissions($remoteFile, [
    'owner' => 'r-w', // read & write
    'group' => 'w',
    'world' => 'w-r-e'
]);

// or you can use the UNIX file permission digits 
$client->setPermissions($remoteFile, 777);
```

#### is methods

```php
// is an ftp directory ?
$client->isDir($remoteDir);

// is a file type ?
$client->isFile($remoteFile);

// is an empty file/directory ?
$client->isEmpty($remoteFile);

// is exists on the FTP server ?
$client->isExists($remoteFile);

// is the server support the size feature ?
$client->isFeatureSupported('SIZE');
```

#### others 

```php
// get the last modified time of the giving file (not working with directories)
$client->lastMTime($remoteFile);

// get a content of an FTP file
$client->getFileContent($remoteFile);

// get all supported features by the FTP server
$client->getFeatures();

// get the server system
$client->getSystem();

// send a request to allocate a space of bytes for the next transfer operation
// note: not all servers requires this
$client->allocateSpace(2048);

// prevent the server from closing the connection and keeping it alive
$client->keepConnectionAlive();
```

You can see all the methods [here](docs/FtpClient.md).

## More documentation

 * [Manipulate an FTP connection with **FtpConnectionInterface**][1]
 * [Configure the connection with **FtpConfig**][2]
 * [The base class **FtpClient**][3]
 * [Sending FTP raw commands with **FtpCommand**][4]
 * [Using the **FtpWrapper** Directly][5]
 * [How i can run tests ?][6]
 
[1]: docs/FtpConnectionInterface.md
[2]: docs/FtpConfig.md
[3]: docs/FtpClient.md
[4]: docs/FtpCommand.md
[5]: docs/FtpWrapper.md
[6]: docs/tests.md

## License

MIT License. please see the [LICENSE FILE](LICENSE) for more information. 