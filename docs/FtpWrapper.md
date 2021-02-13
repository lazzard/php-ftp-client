## Using the FtpWrapper

You can use the `FtpWrapper` as a proxy class to the FTP extension functions (ftp_*), this class handle the FTP errors whenever an error occurs, and you can get the error message sent by the remote server using the `getFtpErrorMessage` method.

```php
$wrapper = new FtpWrapper($connection);
$wrapper->pasv(true);

if (!$wrapper->nlist('www/public_html')) {
    // The 'FtpWrapper' detects and catch FTP errors sent by the server
    // and you can get the last error message by calling the 'getFtpErrorMessage' method
    throw new FtpClientException($wrapper->getFtpErrorMessage());
}
```

### Note 

Sometimes when an error occurs the remote server may not send any specific message, in this case our `getFtpErrorMessage` will return `null`, one solution to this is to create your own error message upon on what you try to do, this is a simple example: 

```php
$wrapper = new FtpWrapper($connection);

if (!$wrapper->pasv()) {
    throw new RuntimeException($wrapper->getFtpErrorMessage() 
        ?: "Unable to turn on the passive mode.");
}
```
