# Lazzard/FtpClient

[![Stable Version](https://img.shields.io/packagist/v/lazzard/php-ftp-client?color=success&label=stable)](https://packagist.org/packages/lazzard/php-ftp-client)
![Tested on](https://img.shields.io/badge/tested-5.6.4-blue)
[![Minimum PHP version](https://img.shields.io/packagist/php-v/lazzard/php-ftp-client)](https://packagist.org/packages/lazzard/php-ftp-client)
[![Downlaods](https://img.shields.io/packagist/dt/lazzard/php-ftp-client?color=blueviolet&style=social)](https://packagist.org/packages/lazzard/php-ftp-client)

> **Lazzard/FtpClient** provides high flexibility to deal with FTP transfer mechanisms in PHP, and allows you to communicate with FTP servers in a more comfortable way than using FTP extension functions directly, in addition to many great features.

*Note: This library aimed to be a full FTP/SFTP client solution for old (5.5+) and newer PHP releases (7.2+) that support FTP extension.*

## Requirements

* PHP version >= 5.5.0.
* FTP extension enabled.

## Installation

### Via composer (recommended)

```console
composer require lazzard/php-ftp-client
```

### Using git

```bash
git clone https://github.com/lazzard/php-ftp-client
```

### Autoloder

This library uses the PSR-4 autoloading mechanism, to generate the autoloader class run this command:

```console
composer dumpautoload
```

## Create an FTP connection

To uses this library you need first to create an FTP connection using one of  these classes:

* `FtpConnection`    : Regular FTP connection (Not secure). 
* `FtpSSLConnection` : FTP over TLS/SSL connection (Secure).

**A simple example would be:**

```php
$connection = new FtpConnection('localhost', 'foo', '1234');
$connection->open();
```

## Configure an FTP connection

After creating an FTP connection you may need to set some options like turning the connection to the passive mode, well for that we provide the `FtpConfig` class that includes methods to manage the FTP connection and set its runtime options.


**option**       | default | description
---              |---      |---
passive          | false   | Turning the passive mode ON/OFF.
timeout          | 90      | Sets timeout value of all FTP transfer operations.
autoSeek         | true    | Should be sets to true.

**Example of turning on the passive mode:**

```php
$config = new FtpConfig($connection);
$config->setPassive(true);
```

## FtpClient

**FtpClient** class is the base class of the library, it contains all the methods you need to start working with your FTP server.

```php
$ftp = new FtpClient($connection);
```

### FtpClient methods

**Method**         | Description
---                |---
`allocateSpace($bytes)` | Sends a request to FTP server to allocate a space for the next file transfer.
`asyncDownload($remoteFile, $localFile, $doWhileDownloading, $resume = true, $interval = 1, $mode = FTP_BINARY)` | Retrieves a remote file asynchronously (non-blocking).
`asyncUpload($remoteFile, $localFile, $doWhileDownloading, $resume = true, $interval = 1, $mode = FTP_BINARY)` | Uploading a local file asynchronously to the remote server.
`back()`             | Back to the parent directory.    
`createDirectory($directory)`  | Creates a directory on the FTP server.
`createFile($fileName, $content = null)`       | Create a file on the FTP server and inserting the giving content to it.
`dirSize($directory)`          | Gets remote directory size.
`download($remoteFile, $localFile, $resume = true, $mode = FTP_BINARY)`  | Starts downloading a remote file.
`fileSize($remoteFile)` | Gets a regular remote file size.
`getConnection()`    | Gets FtpClient connection.
`getCount($directory, $recursive = false, $filter = self::FILE_DIR_TYPE, $ignoreDots = false)` | Gets files count in the giving directory.
`getCurrentDir()`    | Gets current working directory.
`getDefaultTransferType()` | Gets the default transfer type of the FTP server.
`getFeatures()` | Gets additional commands supported by the FTP server outside the basic commands defined in RFC959.
`getFileContent($remoteFile)` | Reads the remote file content and returns the data as a string.
`getParent()` | Gets parent directory of the current working directory.
`getSystem()` | Gets operating system type of the FTP server.
`isDir($remoteFile)` | Checks whether if the giving file is a directory or not.
`isEmpty($remoteFile)` | Checks whether if the giving file/directory is empty or not.
`isExists($remoteFile)` | Checks whether the giving file or directory exists.
`isFeatureSupported($feature)` | Determines if the giving feature is supported by the remote server or not.
`isFile($remoteFile)` | Checks if the giving file is a regular file.
`keepConnectionAlive()` | Sends a request to the server to keep the control channel alive and prevent the server from disconnecting the session.
`lastMTime($remoteFile, $format = null)` | Gets last modified time of an FTP remote regular file.
`listDirectory($directory, $filter = self::FILE_DIR_TYPE, $ignoreDots = true)` | Gets list of files names in the giving directory.
`listDirectoryDetails($directory, $recursive = false, $filter = self::FILE_DIR_TYPE, $ignoreDots = true)` | Gets detailed list of the files in the giving directory.
`move($source, $destination)` | Moves file or a directory to another path.
`removeDirectory($directory)` | Deletes a directory on the FTP server.
`removeFile($remoteFile)` | Deletes regular remote file.
`rename($remoteFile, $newName)` | Renames file/directory on the FTP server.
`setCurrentDir($directory)` | Changes current working directory to the specified directory.
`setPermissions($filename, $mode)` | Sets permissions on FTP file or directory.
`upload($localFile, $remoteFile, $resume = true, $mode = FTP_BINARY)` | Starts uploading the giving local file to the FTP server.

### Asynchronous transfer operations

`FtpClient::asyncDownload` & `FtpClient::asyncUpload` methods accepts a callback function as a third parameter, it will execute every specified `interval`. If no `interval` specified the default sets to **1 second**, the callback function also accepts **an array** that provides some useful information about the transfer operation at the specified interval.
 
**An example of downloading an FTP file asynchronously:** 

```php
$interval = 1;
$ftp->asyncDownload('illustrations/assets.zip', 'assets.zip', function ($stat) use ($interval) {
    ob_end_clean();
    ob_start();

    echo sprintf(
        "speed : %s KB/%ss | percentage : %s%% | transferred : %s KB | second now : %s <br>",
        $stat['speed'],
        $interval,
        $stat['percentage'],
        $stat['transferred'],
        $stat['seconds']
    );

    ob_flush();
    flush();
}, true, $interval);
```

**Result in the browser :** 

![asyncDownload](https://user-images.githubusercontent.com/49124992/82462957-bed5f700-9aab-11ea-95e3-2821254570a6.gif).

## FtpCommand

`FtpCommand` class provides a simple interface to the FTP extension raw functions.


**An Example of `raw($command)` method**: 

```php
$command = new FtpCommand($connection);
$response = $command->raw('SITE HELP');
var_dump($response);
```

**Output:** 

```text
array (size=5)
  'response' => 
    array (size=6)
      0 => string '214-The following SITE commands are recognized' (length=46)
      1 => string ' ALIAS' (length=6)
      2 => string ' CHMOD' (length=6)
      3 => string ' IDLE' (length=5)
      4 => string ' UTIME' (length=6)
      5 => string '214 Pure-FTPd - http://pureftpd.org/' (length=36)
  'code' => int 214
  'message' => string '-The following SITE commands are recognized' (length=43)
  'body' => 
    array (size=4)
      0 => string ' ALIAS' (length=6)
      1 => string ' CHMOD' (length=6)
      2 => string ' IDLE' (length=5)
      3 => string ' UTIME' (length=6)
  'success' => boolean true
```

## Using the FtpWrapper

You can also just use the `FtpWrapper` for calling FTP extension functions (ftp_*).

```php
$connection = new FtpConnection('localhost', 'foo', '1234');
$connection->open();

$wrapper = new FtpWrapper($connection);
$wrapper->pasv(true);

if (!$wrapper->nlist('www/public_html')) {
    // The 'FtpWrapper' detects and catch FTP errors sent by the server
    // and you can get the last error message by calling the 'getFtpErrorMessage' method
    throw new FtpClientException($wrapper->getFtpErrorMessage());
}
```

## Full Example

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
    var_dump($client->getFeatures());
    
    // Close connection
    $connection->close();
} catch (FtpClientException $ex) { // Exceptions handling
    echo $ex->getMessage();
}
```

## Tests

This library uses PHPUnit for testing.

### Requirements

* phpUnit 4.x.x
* php >= 5.5

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

## Contribution

All contributions are welcome, for features/improvements ideas check `TODO.md`. Thank you!

## Maintainer

Developed with ❤ by [El Amrani Chakir](https://github.com/AmraniCh).