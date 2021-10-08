# TODO LIST

This document describes some desired features, API methods, and PHP/PHPUnit upgrades for the feature releases of this library.

## The next PHP Upgrades

- [ ] ^7.4 | ^8.0.

## The next PHPUnit Upgrade

- [ ] PHPUnit 9 (>= PHP 7.3).

## API methods

- [ ] Create a helper method for the `ftp_append` function.
- [ ] Add `FtpClient::getTransferMode($file)` method to find the appropriate transfer mode (not based on file extension) for the giving **local** file.
- [ ] Implement a method that allows to download all the files within the giving remote directory.

## Not wrapped FTP extension functions - why ?

- [ ] [ftp_quit](https://www.php.net/manual/en/function.ftp-quit.php) - is just an alias of [ftp_close](https://www.php.net/manual/en/function.ftp-close.php) function.
- [ ] [ftp_np_put](https://www.php.net/manual/en/function.ftp-nb-put.php) - using [ftp_np_fput](https://www.php.net/manual/en/function.ftp-nb-fput.php) instead for the upload progress.
