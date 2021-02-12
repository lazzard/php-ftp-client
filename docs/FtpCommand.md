## FtpCommand

`FtpCommand` class provides a simple interface to the FTP extension command functions.

**method**       | description
---              |---     
`raw($command)`  | Sends an arbitrary command to the FTP server.
`site($command)` | Sends a request to execute a SITE command.
`exec($command)` | Sends a request to execute the provided executable on the server.
`supprtedSiteCommands()` | Sends a SITE HELP command to the FTP server and returns the supported SITE commands.

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

**Note** : the success value depends on the FTP reply code, generally if the command not accepted temporary (4yz) or permanently (5yz) by the FTP server it will considered unsuccessful command, some commands are considered as unsuccessful on (3yz) code series reply as well- see RFC959 for more information.
