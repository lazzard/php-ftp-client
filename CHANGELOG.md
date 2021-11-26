# Change Log

## 1.5.3 (2021-11-26)

* Added new method `FtpClient::appendFile`.
* Added getters and setters for various classes (see [commit](https://github.com/lazzard/php-ftp-client/commit/02df6b9be719a236701c2bcb78f990632131ffae)).
* Removed the deprecated `ConnectionInterface::isSecure`.
* Removed the deprecated `ConnectionInterface::isPassive`.
* `FtpClient::fileSize` is now throw exception if the giving file is a directory type or an error occurs.

## 1.5.0 (2021-10-08)

* Upgraded the code base to PHP v7.2.
* Upgraded PHPUnit to ^8.0.
* `FtpCommand::raw` is now throw an exception in failure.
* `FtpWrapper::getErrorMessage` returns empty string instead of null if no error message is available.
* `FtpClient::getFeatures` throws exception in failure.
* Fixed `FtpClient::createDir` for multiple directory creation.
* `FtpClient::getFileContent` now throws exception if the passed file is a directory type instead of returning false value.
* Fixed PHPDoc for some methods.

## 1.4.2 (2021-10-01)

* Fixed `FtpClient::getFileContent` to get the correct file content for binary files ([#20](../../issues/20)).
* Added a new optional parameter `$mode` to `FtpClient::getFileContent` to specify the FTP transfer mode that will be used to get the files content.
* Fixed `FtpClient::listDirDetails` for FTP servers that do not send the DOTS files pointers in directories listing operations ([#21](../../issues/21)).

## 1.4.1 (2021-08-20)

* Fixed a bug with `FtpClient::listDir` ([#17](../../issues/17)).
* Deprecated `ConnectionInterface::isPassive`.

## v1.4.0 (2021-08-08)

* `FtpClient::fileSize` fixed for servers that not support `SIZE` feature.
* `FtpClient::listDir` fixed compatibility issue with some FTP servers.
* `Connection::isConnected` fixed bug : if the connection is not established yet the method was returned 
a NULL value instead of false.
* `FtpCommand::raw` improved and added the `end-message` to the returned array.
* `FtpClient::isDir` performance optimized.
* `FtpClient::listDirDetails` improved (No Breaking Change).
* `FtpClient::getFeatures` is now returns false in failure.
* `FtpClient::isFeatureSupported` can now throw a `FtpClientException` exception.
* `ConnectionInterface::isSecure` is deprecated see [#15](../../issues/15).
* The integration tests refactored and optimized.

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
