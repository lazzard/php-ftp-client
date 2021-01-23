## FtpCommand

`FtpCommand` class provides a simple interface to the FTP extension raw functions.


**An Example of `raw($command)` method**: 

```php
$command = new FtpCommand($connection);
$response = $command->raw('SITE HELP');
var_dump($response);
```

**Output:** 

```text
array (size=5)
  'response' => 
    array (size=6)
      0 => string '214-The following SITE commands are recognized' (length=46)
      1 => string ' ALIAS' (length=6)
      2 => string ' CHMOD' (length=6)
      3 => string ' IDLE' (length=5)
      4 => string ' UTIME' (length=6)
      5 => string '214 Pure-FTPd - http://pureftpd.org/' (length=36)
  'code' => int 214
  'message' => string '-The following SITE commands are recognized' (length=43)
  'body' => 
    array (size=4)
      0 => string ' ALIAS' (length=6)
      1 => string ' CHMOD' (length=6)
      2 => string ' IDLE' (length=5)
      3 => string ' UTIME' (length=6)
  'success' => boolean true
```
