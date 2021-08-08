# TODO LIST

## The next PHP Upgrades

- [ ] 7.2v.
    - [ ] Create a helper method for the `ftp_append` function.
    - [ ] Use `ftp_mlsd` function in `FtpClient::lastMTime` to support directories type.
- [ ] 7.4v.
- [ ] 8.0v.

## The next PHPUnit Upgrade

- [ ] PHPUnit 8 (>= PHP 7.2).
- [ ] PHPUnit 9 (>= PHP 7.3).

## API methods

- [ ] Add `FtpClient::getTransferMode($file)` method to find the appropriate transfer mode (not based on file extension) for the giving **local** file.
- [ ] Implement a method that allows to download all the files within the giving remote directory.

## Not wrapped FTP extension functions - why ?

- [ ] [ftp_quit](https://www.php.net/manual/en/function.ftp-quit.php) - is just an alias of [ftp_close](https://www.php.net/manual/en/function.ftp-close.php) function.
- [ ] [ftp_np_put](https://www.php.net/manual/en/function.ftp-nb-put.php) - using [ftp_np_fput](https://www.php.net/manual/en/function.ftp-nb-fput.php) instead for the upload progress.
