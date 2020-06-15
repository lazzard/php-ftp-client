# TODO

### Code improvement.
- [ ] Reduce unnecessary error control operators (@).
- [ ] Better FTP functions warning errors handling.

### PHP Upgrade
- [ ] 5.6v
- [ ] 7.2v

### Upcoming client features.
- [ ] `lastMTime()` directory supporting.  
- [ ] File append feature.
- [ ] Supporting implicit FTP connection.
- [ ] Supporting IPV6 protocol transfers **[RFC[2428]](https://tools.ietf.org/html/rfc2428)**.
- [ ] (Performance) Caching functions results.

### Not wrapped FTP extension functions - why ?
- [ ] [ftp_mlsd](https://www.php.net/manual/en/function.ftp-append.php) - not supported in PHP5. 
- [ ] [ftp_append](https://www.php.net/manual/en/function.ftp-mlsd.php) - not supported in PHP5. 
- [ ] [ftp_quit](https://www.php.net/manual/en/function.ftp-quit.php) - is just an alias of [ftp_close](https://www.php.net/manual/en/function.ftp-close.php) function.
- [ ] [ftp_np_put](https://www.php.net/manual/en/function.ftp-nb-put.php) - using [ftp_np_fput](https://www.php.net/manual/en/function.ftp-nb-fput.php) instead for the upload progress.

### Testing.