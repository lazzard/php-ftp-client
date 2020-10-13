# TODO

## Code improvement

## PHP Upgrade

- [ ] 5.6v
    - Supporting `FTP_USEPASVADDRESS` runtime option.
- [ ] 7.2v 
    - [ ] Using `ftp_mlsd` function in `FtpConfig::listDirectoryDetails` method to get the Unix timestamps of files/directories.
    - [ ] Using `ftp_append` function to implement a client method allows for appending remote file content.
    - [ ] Using `ftp_mlsd` function in `FtpConfig::lastMTime` to support directories.

## Upcoming client methods

- [ ] Adding `FtpConfig::usePassiveAddress(bool)`.
- [ ] Adding `FtpClient::getTransferMode($file)` method to find the appropriate transfer mode (not based on file extension) for the giving local file.
- [ ] `FtpClient::lastMTime()` directory supporting.  
- [ ] Adding `FtpClient::append($remoteFile, $content)` method.

## Not wrapped FTP extension functions - why ?

- [ ] [ftp_mlsd](https://www.php.net/manual/en/function.ftp-append.php) - not supported in PHP5. 
- [ ] [ftp_append](https://www.php.net/manual/en/function.ftp-mlsd.php) - not supported in PHP5. 
- [ ] [ftp_quit](https://www.php.net/manual/en/function.ftp-quit.php) - is just an alias of [ftp_close](https://www.php.net/manual/en/function.ftp-close.php) function.
- [ ] [ftp_np_put](https://www.php.net/manual/en/function.ftp-nb-put.php) - using [ftp_np_fput](https://www.php.net/manual/en/function.ftp-nb-fput.php) instead for the upload progress.

## Testing