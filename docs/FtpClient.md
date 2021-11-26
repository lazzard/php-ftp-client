# FtpClient

`FtpClient\FtpClient` is the base class of the library, it contains all methods you need to start working with your FTP server, it only takes a connection instance as a first parameter.

```php
// create an FtpClient Instance
$client = new FtpClient(ConnectionInterface $connection);
```

### Available methods 

```php
FtpClient::allocateSpace($bytes)
FtpClient::appendFile(string $remoteFile, string $content, $mode = FtpWrapper::BINARY)
FtpClient::asyncDownload(string $remoteFile, string $localFile, callable $callback, bool $resume = true, int $interval = 1, int $mode = FtpWrapper::BINARY)
FtpClient::asyncUpload(string $localFile, string $remoteFile, callable $callback, bool $resume = true, int $interval = 1, int $mode = FtpWrapper::BINARY)
FtpClient::back()
FtpClient::changeDir(string $directory)
FtpClient::copy(string $remoteSource, string $remoteDirectory)
FtpClient::copyFromLocal(string $source, string $destinationFolder)
FtpClient::copyToLocal(string $remoteSource, string $destinationFolder)
FtpClient::createDir(string $directory)
FtpClient::createFile(string $filename, $content = NULL, int $mode = FtpWrapper::BINARY)
FtpClient::dirSize(string $directory)
FtpClient::download(string $remoteFile, string $localFile, bool $resume = true, int $mode = FtpWrapper::BINARY)
FtpClient::fileSize(string $remoteFile)
FtpClient::find(string $pattern, string $directory, bool $recursive = false)
FtpClient::getConnection()
FtpClient::getCount(string $directory, bool $recursive = false, int $filter = self::FILE_DIR_TYPE, bool $ignoreDots = true)
FtpClient::getCurrentDir()
FtpClient::getFeatures()
FtpClient::getFileContent(string $remoteFile, int $mode = FtpWrapper::BINARY)
FtpClient::getParent()
FtpClient::getSystem()
FtpClient::getTransferType()
FtpClient::getWrapper()
FtpClient::isDir(string $remoteFile)
FtpClient::isEmpty(string $remoteFile)
FtpClient::isExists(string $remoteFile)
FtpClient::isFeatureSupported(string $feature)
FtpClient::isFile(string $remoteFile)
FtpClient::keepAlive()
FtpClient::lastMTime(string $remoteFile, string $format = NULL)
FtpClient::listDir(string $directory, int $filter = self::FILE_DIR_TYPE, bool $ignoreDots = true)
FtpClient::listDirDetails(string $directory, bool $recursive = false, int $filter = self::FILE_DIR_TYPE, bool $ignoreDots = true)
FtpClient::move(string $source, string $destinationFolder)
FtpClient::removeDir(string $directory)
FtpClient::removeFile(string $remoteFile)
FtpClient::rename(string $remoteFile, string $newName)
FtpClient::setCommand(FtpCommand $command)
FtpClient::setConnection(ConnectionInterface $connection)
FtpClient::setPermissions(string $filename, $mode)
FtpClient::setWrapper(FtpWrapper $wrapper)
FtpClient::upload(string $localFile, string $remoteFile, bool $resume = true, int $mode = FtpWrapper::BINARY)
```

### Asynchronous transfer operations

`FtpClient::asyncDownload` & `FtpClient::asyncUpload` methods accepts a callback function as a third parameter, this callback function will execute every specified `interval`. If no `interval` specified the default sets to **1 second**, the callback function also accepts **an array** that provides some useful information about the transfer operation at the specified interval.
 
**This is an example of downloading an FTP file asynchronously:** 

```php
$interval = 1;
$client->asyncDownload('path/to/remote/file', 'path/to/local/file', function ($state) use ($interval) {
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
