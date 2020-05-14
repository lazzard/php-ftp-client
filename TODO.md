# TODO

### Code improvement.
- [ ] FtpClient upload/download functions takes too much of lines, consider to do some refactoring, or extract an interface and implement these methods in a separate class. 
- [ ] Reduce unnecessary error control operators (@).
- [ ] Better FTP functions errors handing.

### Upcoming client features.
- [ ] `lastMTime()` directory supporting.  
- [ ] File append feature.
- [ ] Supporting implicit FTP connection.
- [ ] Supporting IPV6 protocol transfers **[RFC[2428]](https://tools.ietf.org/html/rfc2428)**.
- [ ] (Performance) Caching functions results.
- [ ] PHP7 upgrade.

### Not wrapped FTP extension functions - why ?.
- [ ] [ftp_mlsd](https://www.php.net/manual/en/function.ftp-append.php) - not supported in PHP5. 
- [ ] [ftp_append](https://www.php.net/manual/en/function.ftp-mlsd.php) - not supported in PHP5. 
- [ ] [ftp_quit](https://www.php.net/manual/en/function.ftp-quit.php) - is just an alias of [ftp_close](https://www.php.net/manual/en/function.ftp-close.php) function.
- [ ] [ftp_np_put](https://www.php.net/manual/en/function.ftp-nb-put.php) - using [ftp_np_fput](https://www.php.net/manual/en/function.ftp-nb-fput.php) instead for the upload progress.

### Functions to implement
- [ ] `auth_ssl()` **`AUTH`** - [RFC2228](https://tools.ietf.org/html/rfc2228).
- [ ] `auth_tls()` **`AUTH`** - [RFC2228](https://tools.ietf.org/html/rfc2228).
- [ ] `prot($level)` **`PROT`** - [RFC2228](https://tools.ietf.org/html/rfc2228).
- [ ] `pbsz($size)` **`PBSZ`** - [RFC2228](https://tools.ietf.org/html/rfc2228).
- [ ] `clear_cmd()` **`CCC`** -  [RFC2228](https://tools.ietf.org/html/rfc2228).
- [ ] `mlsd($directory)` : available only in PHP7.
- [ ] `append($remoteFile)` : available only in PHP7.

### Testing.
- [ ] FtpSSLConnection testing.

### Done ✓