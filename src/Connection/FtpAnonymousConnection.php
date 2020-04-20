<?php


namespace Lazzard\FtpClient\Connection;


class FtpAnonymousConnection extends FtpConnection
{

    public function __construct(
        $host, $username = "anonymous", $password = "guest", $port = 21, $timeout = 90
    )
    {
        parent::__construct($host, $username, $password, $port, $timeout);
    }

}