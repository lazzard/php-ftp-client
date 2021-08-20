# FtpWrapper

You can use the `FtpWrapper` directly as a proxy class to the FTP extension functions (ftp_*), this class can handle FTP errors that may be raised with the built-in functions, and you can access the last error message using the `FtpWrapper::getErrorMessage` method.  

**Simple Example :**

```php
$wrapper = new FtpWrapper(ConnectionInterface $connection);
$wrapper->pasv(true);

if (!$wrapper->nlist('www/public_html')) {
    // The 'FtpWrapper' detects and catch FTP errors sent by the server
    // and you can get the last error message by calling the 'getFtpErrorMessage' method
    throw new \RuntimeException($wrapper->getErrorMessage());
}
```

**Note :** 

Sometimes when an error occurs the remote server may not send any specific message, in this case our `getFtpErrorMessage` will return `null`, one solution to this is to create your own error message upon on what you try to do, this is a simple example: 

```php
$wrapper = new FtpWrapper($connection);

if (!$wrapper->pasv()) {
    throw new \RuntimeException($wrapper->getErrorMessage() 
        ?: "Unable to turn on the passive mode.");
}
```
