# FtpConfig

After create an FTP connection you may need to set some options like turning connection to the passive mode, well for that we provide the `FtpConfig` class that includes methods to configure the FTP connection and change its runtime options.

option            | class method      | default | description
---               |---                |---      |---
passive           | setPassive        | false   | Turning the passive mode ON/OFF.
timeout           | setTimeout        | 90      | Sets timeout value of all FTP transfer operations.
autoSeek          | setAutoSeek       | true    | This should be set to true for resuming transfer operations.
usePassiveAddress | usePassiveAddress | true    | Whether or not to use the passive IP address returned after sending the passive command through the control channel.

**Example :**

```php
$config = new FtpConfig(ConnectionInterface $connection);

// Setters
$config->setPassive(true);
$config->setTimeout(90);
$config->usePassiveAddress(true);
$config->setAutoSeek(true);

// Getters
$config->getTimeout();
$config->isAutoSeek();
$config->isUsePassiveAddress();
```
