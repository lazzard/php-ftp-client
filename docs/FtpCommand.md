# FtpCommand

`FtpClient\Command\FtpCommand` class provides a simple methods to send commands and recieve responses from your FTP server.

**method**       | description
---              |---     
`raw($command)`  | Sends an arbitrary command to the FTP server.
`site($command)` | Sends a request to execute a SITE command.
`exec($command)` | Sends a request to execute the provided executable on the server.
`supprtedSiteCommands()` | Sends a SITE HELP command to the FTP server and returns the supported SITE commands.

**An Example of `raw($command)` method**: 

```php
$command = new FtpCommand(ConnectionInterface $connection);

$response = $command->raw('SITE HELP');

print_r($response);
```

**Output:** 

```text
Array
(
    [response] => Array
        (
            [0] => 214-The following SITE commands are recognized (* =>'s unimplemented)
            [1] =>  HELP
            [2] =>  CHGRP
            [3] =>  CHMOD
            [4] => 214 Direct comments to root@localhost
        )

    [code] => 214
    [message] => The following SITE commands are recognized (* =>'s unimplemented)
    [body] => Array
        (
            [0] =>  HELP
            [1] =>  CHGRP
            [2] =>  CHMOD
        )

    [end-message] => 214 Direct comments to root@localhost
    [success] => 1
)
```

**Note** : the success value depends on the FTP reply code, generally if the command not accepted temporary (4yz) or permanently (5yz) by the FTP server it will considered unsuccessful command, some commands are considered as unsuccessful on (3yz) code series reply as well- see [RFC959](https://tools.ietf.org/html/rfc959) for more information.
