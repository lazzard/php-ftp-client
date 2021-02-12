## FtpClient

**FtpClient** class is the base class of the library, it contains all the methods you need to start working with your FTP server.

```php
// Create an FtpClient Instance
$client = new FtpClient($connection);
```

### Available methods 

```php
FtpClient::__construct($connection)
FtpClient::allocateSpace($bytes)
FtpClient::asyncDownload($remoteFile, $localFile, $doWhileDownloading, $resume = true, $interval = 1, $mode = FTP_BINARY)
FtpClient::asyncUpload($localFile, $remoteFile, $doWhileDownloading, $resume = true, $interval = 1, $mode = FTP_BINARY)
FtpClient::back()
FtpClient::chmodToFileType($chmod)
FtpClient::chmodToNumeric($chmod)
FtpClient::createDirectory($directory)
FtpClient::createFile($remoteFile, $content = NULL)
FtpClient::dirSize($directory)
FtpClient::download($remoteFile, $localFile, $resume = true, $mode = FTP_BINARY)
FtpClient::fileSize($remoteFile)
FtpClient::getConnection()
FtpClient::getCount($directory, $recursive = false, $filter = self::FILE_DIR_TYPE, $ignoreDots = true)
FtpClient::getCurrentDir()
FtpClient::getDefaultTransferType()
FtpClient::getFeatures()
FtpClient::getFileContent($remoteFile)
FtpClient::getParent()
FtpClient::getSystem()
FtpClient::isDir($remoteFile)
FtpClient::isEmpty($remoteFile)
FtpClient::isExists($remoteFile)
FtpClient::isFeatureSupported($feature)
FtpClient::isFile($remoteFile)
FtpClient::keepConnectionAlive()
FtpClient::lastMTime($remoteFile, $format = NULL)
FtpClient::listDirectory($directory, $filter = self::FILE_DIR_TYPE, $ignoreDots = true)
FtpClient::listDirectoryDetails($directory, $recursive = false, $filter = self::FILE_DIR_TYPE, $ignoreDots = true)
FtpClient::move($source, $destinationFolder)
FtpClient::removeDirectory($directory)
FtpClient::removeFile($remoteFile)
FtpClient::rename($remoteFile, $newName)
FtpClient::setCommand($command)
FtpClient::setCurrentDir($directory)
FtpClient::setPermissions($filename, $mode)
FtpClient::setWrapper($wrapper)
FtpClient::upload($localFile, $remoteFile, $resume = true, $mode = FTP_BINARY)
```

### Asynchronous transfer operations

`FtpClient::asyncDownload` & `FtpClient::asyncUpload` methods accepts a callback function as a third parameter, it will execute every specified `interval`. If no `interval` specified the default sets to **1 second**, the callback function also accepts **an array** that provides some useful information about the transfer operation at the specified interval.
 
**This is an example of downloading an FTP file asynchronously:** 

```php
$interval = 1;
$client->asyncDownload('illustrations/assets.zip', 'assets.zip', function ($state) use ($interval) {
    ob_end_clean();
    ob_start();

    echo sprintf(
        "speed : %s KB/%ss | percentage : %s%% | transferred : %s KB | second now : %s <br>",
        $state['speed'],
        $interval,
        $state['percentage'],
        $state['transferred'],
        $state['seconds']
    );

    ob_flush();
    flush();
}, true, $interval);
```

**Result in the browser should be as following :** 

![asyncDownload](https://user-images.githubusercontent.com/49124992/82462957-bed5f700-9aab-11ea-95e3-2821254570a6.gif).