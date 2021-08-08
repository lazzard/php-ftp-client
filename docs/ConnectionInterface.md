# ConnectionInterface

`FtpClient\Connection\ConnectionInterface` interface provides an easy way to manipulate an FTP connection instance.

You can use the following classes that implement the interface.

 * `FtpConnection` : Regular FTP connection (Not secure). 
 * `FtpSSLConnection` : FTP over TLS/SSL connection (Secure).

**Example :**

```php
// create an FTP connection instance
$connection = new FtpConnection('localhost', 'foo', '1234');

// open the FTP connection
$connection->open();

// close the connection
$connection->close();

// getters 
$connection->getStream();
$connection->getHost();
$connection->getPort();
$connection->getTimeout();
$connection->getUsername();
$connection->getPassword();
$connection->isConnected();
$connection->isPassive();
```
