# Lazzard/FtpClient

![Release version](https://img.shields.io/badge/release-1.0.0--RC1-orange)
![Tested on](https://img.shields.io/badge/tested-5.6.4-green)
![Minimum PHP version](https://img.shields.io/badge/php-%3E%3D5.5.0-blue)

> **Lazzard/FtpClient** provides high flexibility to deal with FTP transfer mechanisms in PHP, and allows you to communicate with FTP servers in a more comfortable way than using FTP extension functions directly, in addition to many great features.

# Contents

- [Lazzard/FtpClient](#lazzardftpclient)
- [Contents](#contents)
- [Requirements](#requirements)
- [Get started](#get-started)
  - [Installation](#installation)
    - [Via composer (recommended)](#via-composer-recommended)
    - [Using git](#using-git)
    - [Autoloder](#autoloder)
  - [Setup](#setup)
    - [FtpBaseConfig [optional]](#ftpbaseconfig-optional)
        - [Check if FTP extension is loaded and throw an exception if not.](#check-if-ftp-extension-is-loaded-and-throw-an-exception-if-not)
        - [Override Php limits configs.](#override-php-limits-configs)
    - [Connection setup](#connection-setup)
    - [FtpConfig](#ftpconfig)
- [Usage](#usage)
  - [FtpClient](#ftpclient)
    - [FtpClient methods :](#ftpclient-methods-)
    - [Asynchronous transfer operations](#asynchronous-transfer-operations)
  - [FtpCommand](#ftpcommand)
  - [Full Example](#full-example)
- [Tests](#tests)
  - [Requirements :](#requirements-)
  - [Installation](#installation-1)
  - [Tests configs](#tests-configs)
  - [Run tests :](#run-tests-)

# Requirements
* PHP version >= 5.5.0
* FTP extension

# Get started 

## Installation

### Via composer (recommended)

```bash
    composer require lazzard/php-ftp-client
```

### Using git

```bash
    git clone https://github.com/lazzard/php-ftp-client
```

### Autoloder
This library uses the PSR-4 autoloding mechanism, to generate the autoloder class run this command:

```bash
    composer install --no-dev
```

## Setup

### FtpBaseConfig [optional]

To correctly setup an appropriate environment for you FTP application the `FtpBaseConfig` class was provided for that :

##### Check if FTP extension is loaded and throw an exception if not.

```php
<?php
    Lazzard\FtpClient\Config\FtpBaseConfig::isFtpExtensionLoaded();
```

##### Override Php limits configs.

To overwrite php directives use `setPhpLimit($iniConfig)`, an example :

```php
<?php
    Lazzard\FtpClient\Config\FtpBaseConfig::setPhpLimit([
        "maxExecutionTime" => 0,
        "ignoreUserAbort"  => true,
        "memoryLimit"      => 512
    ]);
```

**Supported options :**

- "**maxExecutionTime**" : Specifies the maximum execution time in **seconds** of the **current** script, setting this option to an **appropriate value** may be useful to prevent the script from closing and canceling the current transfer process.
- "**ignoreUserAbort**"  : When enabled, the **current** script continues execution in the background even if the connection stopped by the user (e.g Closing the browser window).
- "**memoryLimit**"      : The maximum memory limit in **Mega Bytes** allowed to allocate for the **current** script process.
 
### Connection setup

**Supported FTP connections :** 

* `FtpConnection`    : Regular FTP connection (Not secure). 
* `FtpSSLConnection` : FTP over TLS/SSL connection (Secure).

**Example:**

```php
<?php
    $connection = new Lazzard\FtpClient\Connection\FtpConnection('localhost', 'foo', '1234');
    $connection->open();
```

**Connection interface methods :** 

**method**       | description  
---              |---      
`open()`         | Opens an FTP connection. 
`close()`        | Closes the FTP connection.
`getStream()`    | Gets FTP stream resource.
`getHost()`      | Gets FTP host name.
`getPort()`      | Gets FTP port.
`getTimeout()`   | Gets FTP stream timeout.
`getUsername()`  | Gets username.
`getPassword()`  | Gets password.


### FtpConfig

`FtpConfig` class includes methods to manage the FTP connection and set its runtime options :

**option**       | default | description
---              |---      |---
passive          | false   | Turning the passive mode ON/OFF.
timeout          | 90      | Sets the timeout of all FTP transfer operations.
autoSeek         | true    | Should be sets to true for the transfer resuming operations.
initialDirectory | /       | The '/' is the primary root by default.

**method 1 - Pass an array to the `FtpConfig` object like so :**

```php
<?php
    $config = new Lazzard\FtpClient\Config\FtpConfig($connection, [
        'passive' => true,
        'initialDirectory' => 'public_html/'
    ]);
```

**method 2 - Use the object methods :** 

```php
<?php
    $config = (new Lazzard\FtpClient\Config\FtpConfig($connection))->setPassive(true);
```
# Usage

## FtpClient

**FtpClient** class is the base class of the library, its contains all the methods you need to start working with your FTP server.

```php
<?php
    $ftp = new Lazzard\FtpClient\FtpClient($connection);
```

### FtpClient methods : 

**method**         | description
---                |---
`allocateSpace($bytes)` | Sends a request to FTP server to allocate a space for the next file transfer.
`asyncDownload($remoteFile, $localFile, $doWhileDownloading, $resume = true, $interval = 1, $mode = FtpWrapper::BINARY)` | Retrieves a remote file asynchronously (non-blocking).
`asyncUpload($remoteFile, $localFile, $doWhileDownloading, $resume = true, $interval = 1, $mode = FtpWrapper::BINARY)` | Uploading a local file asynchronously to the remote server.
`back()`             | Back to the parent directory.    
`createDirectory($directory)`  | Creates a directory on the FTP server.
`createFile($fileName, $content = null)`       | Create a file on the FTP server and inserting the giving content to it.
`dirSize($directory)`          | Gets remote directory size.
`download($remoteFile, $localFile, $resume = true, $mode = FtpWrapper::BINARY)`  | Starts downloading a remote file.
`fileSize($remoteFile)` | Gets a regular remote file size.
`getConnection()`    | Gets FtpClient connection.
`getCount($directory, $recursive = false, $filter = self::FILE_DIR_TYPE, $ignoreDots = false)` | Gets files count in the giving directory.
`getCurrentDir()`    | Gets current working directory.
`getDefaultTransferType()` | Gets the default transfer type of the FTP server.
`getFeatures()` | Gets additional commands supported by the FTP server outside the basic commands defined in RFC959.
`getFileContent($remoteFile)` | Reads the remote file content and returns the data as a string.
`getParent()` | Gets parent directory of the current working directory.
`getSystem()` | Gets operating system type of the FTP server.
`getTransferMode($fileName)` | Gets appropriate transfer mode of the giving file.
`isDir($remoteFile)` | Checks whether if the giving file is a directory or not.
`isEmptyDirectory($directory)` | Checks whether if the giving directory is empty or not.
`isEmptyFile($remoteFile)` | Checks if the remote file is empty or not.
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
`upload($localFile, $remoteFile, $resume = true, $mode = FtpWrapper::BINARY)` | Starts uploading the giving local file to the FTP server.

### Asynchronous transfer operations

`asyncDownload` / `asyncUpload` accepts a callback function as a third parameter, it will execute every specified `interval`, if no `interval` specified the default sets to **1 second**, the callback function also accepts **an array** that provides some useful information about the transfer operation at the specified interval.
 
**An example of downloading an FTP file asynchronously :** 

```php
<?php
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

`FtpCommand` class provides a simple interface to the FTP extension raw functions, the following methods are supported :

**method**       | description
---              |---     
`raw($command)`  | Sends an arbitrary command to the FTP server.
`site($command)` | Sends a request to execute a SITE command.
`exec($command)` | Sends a request to execute the provided executable on the server.
`supprtedSiteCommands()` | Sends a SITE HELP command to the FTP server and returns the supported SITE commands.

`raw($command)` example : 

```php
<?php
    $command = new Lazzard\FtpClient\Command\FtpCommand($connection);
    $response = $command->raw('SITE HELP');
    var_dump($response);
```

**Output :** 

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

**Note** : the success value depends on the FTP reply code, generally if the command not accepted temporary **(4yz)** or permanently **(5yz)** by the FTP server it will considered unsuccessful command, some commands are considered as unsuccessful on **(3yz)** code series reply as well- see [RFC959](https://tools.ietf.org/html/rfc959#section-4) for more information.

## Full Example

```php
<?php
    try {
        // FtpBaseConfig
        FtpBaseConfig::isFtpExtensionLoaded();
        FtpBaseConfig::setPhpLimit([
            'maxExecutionTime' => 0,
            'ignoreUserAbort' => true
        ]);
        
        // Connection
        $connection = new FtpConnection("localhost", "foo", "12345");
        $connection->open();
        
        // FtpConfig
        $config = new FtpConfig($connection);
        $config->setPassive(true);
        
         // FtpClient
        $ftp = new FtpClient($connection);
        
        // Start working
        var_dump($ftp->getFeatures());
        
        // Close connection
        $connection->close();
    } catch (FtpClientException $ex) { // Exceptions handling
        echo $ex->getMessage();
    }
```

# Tests

this library uses phpUnit for testing.

## Requirements :

* phpUnit 4.x.x
* php >= 5.5

## Installation

if you don't have PHPUnit installed globally, or you have a different PHPUnit version run this : 

```bash
    composer install --dev
```

## Tests configs

To run tests properly edit  **tests/config.php** with your FTP credianles.

## Run tests :

```bash
    vendor/bin/phpunit
```



