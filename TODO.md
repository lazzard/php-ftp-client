# TODO

## Improvements

- [ ] `FtpClient::isDir`&`FtpClient::isFile` : consider replacing the SIZE solution because the `ftp_size` returned -1 if an error occurs, and the same value returned if the giving file is a directory type, so we can't trust it.

## PHPUnit Upgrade
- [ ] PHP v^5.6 => Upgrade phpunit to v^5.

## PHP Upgrade

- [ ] 7.2v 
    - [ ] Use `ftp_mlsd` function in `FtpClient::listDirectoryDetails` method to get the Unix timestamps of files/directories.
    - [ ] Use `ftp_append` function to implement a client method allows for appending remote file content.
    - [ ] Use `ftp_mlsd` function in `FtpClient::lastMTime` to support directories type.

## Upcoming client methods

- [ ] Add `FtpClient::getTransferMode($file)` method to find the appropriate transfer mode (not based on file extension) for the giving **local** file.
- [ ] `FtpClient::lastMTime()` directory supporting.  
- [ ] Add `FtpClient::append($remoteFile, $content)` method.
- [ ] Implement a method that allows to download all the files within the giving directory.
- [ ] Add `FtpClient::copy` that serves to copy a remote file or a whole directory to the local machine. 

## Not wrapped FTP extension functions - why ?

- [ ] [ftp_mlsd](https://www.php.net/manual/en/function.ftp-append.php) - not supported in PHP5. 
- [ ] [ftp_append](https://www.php.net/manual/en/function.ftp-mlsd.php) - not supported in PHP5. 
- [ ] [ftp_quit](https://www.php.net/manual/en/function.ftp-quit.php) - is just an alias of [ftp_close](https://www.php.net/manual/en/function.ftp-close.php) function.
- [ ] [ftp_np_put](https://www.php.net/manual/en/function.ftp-nb-put.php) - using [ftp_np_fput](https://www.php.net/manual/en/function.ftp-nb-fput.php) instead for the upload progress.
