# Change Log

## v1.3.5 (2021-06-27)

### Fixed

* `FtpClient::isDir` and `FtpClient::isFile` fixed servers compatibility, this two methods no longer depends on the untrusted `SIZE` feature to work.

### Added

* `FtpClient::copy` method added.

### Behavior changed

* `FtpClient::getFileContent` returns false if the passed file is a directory.

## v1.3.3 (2021-05-02)

### Added

* `FtpClient::copyToLocal` method added.
* `FtpClient::find` method added.

### Renamed 

* `FtpClient::setCurrentDir` renamed to `FtpClient::changeDir`.
* `FtpClient::createDirectory` renamed to `FtpClient::createDir`.
* `FtpClient::removeDirectory` renamed to `FtpClient::removeDir`.
* `FtpClient::getDefaultTransferType` renamed to `FtpClient::getTransferType`.
* `FtpClient::keepConnectionAlive` renamed to `FtpClient::keepAlive`.
* `FtpClient::listDirecory` renamed to `FtpClient::listDir`.
* `FtpClient::listDirecoryDetails` renamed to `FtpClient::listDirDetails`.
* `FtpWrapper::getFtpErrorMessage` renamed to `FtpWrapper::getErrorMessage`.

### Others

* Upgraded PHPUnits version to ^5.

## v1.3.0 (2021-04-05)

* `WrapperException` Added.
* `ConnectionInterface::isPassive` Introduced.

## v1.2.9 (2021-03-09)

* Making methods more atomic.
* Wrapped built-in FTP extension constants, all constants are available in the `FtpWrapper` class.
* `FtpClient::listDirectoryDetails` is now returned an associative array with file paths instead of index integers.
* Fixed `FtpClient::isDir` and `FtpClient::isFile` compatibility with servers that not support SIZE feature.

## v1.2.7 (2021-02-22)

* Added `$mode` parameter for `FtpClient::createFile`  method.

## v1.2.6 (2021-02-12)

* Added `FtpClient\Connection\Connection` abstract class.
* Introduced `ConnectionInterface::isSecure`.
* Introduced `ConnectionInterface::isConnected`.

## v1.2.4 (2021-02-12)

* Introduced `FtpClient::copyFromLocal`.
* Fixed `FtpClient::asyncDownload` method. (#8)
* Improved markdown documentation.

## v1.2.2 (2021-01-24)

* README documentation improved.

## v1.2.0 (2021-01-12)

* Upgraded to PHP 5.6.0 version.
* Supported the `usePassiveAddress` runtime option. 
* Added more options for unit testing (`PASSIVE`, `INITIAL_DIR`).
* Removed `USESSL` option for unit testing.

## v1.1.0 (2020-10-13)

### Removed

* Removed `FtpBaseConfig` class.
* Removed `FtpClient::getTransferMode` method.
* Removed `FtpClient::isEmptyDirectory` & `FtpClient::isEmptyFile` use `FtpClient::isEmpty` instead for both files and directories.

### Behavior changed
    
* `FtpClient::removeFile` & `FtpClient::removeDirectory` methods now doesn't throw an exception if the given file doesn't exist, but instead returns false.
* `FtpClient::createFile` now doesn't throw an exception if the file name already exists on the server, instead, the remote file will be overwritten.
* `FtpClient::createDirectory` now returns true if the giving directory already exists instead of throwing an exception.

### Improved

* FTP error handling improved, the `FtpWrapper` is now responsible for detecting and muting FTP functions errors.
* Unit tests improved.
* Docs improved.

## v1.0.2 (2020-8-17)

* Fixed `isExists` method (#5).
* Fixed error handling of `createDirectory`  & `createFile` methods (#5).

## v1.0.0 (2020-8-15)

* Fixed listDirectoryDetails not working with directories contains spaces. (#2)
* Fixed listDirectoryDetails incorrect file path. (#4)

## v1.0.0-RC1 (2020-5-17)

* First release.
