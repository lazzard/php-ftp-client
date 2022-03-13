<?php

namespace Lazzard\FtpClient\Tests\Integration;

use Lazzard\FtpClient\Connection\FtpConnection;
use Lazzard\FtpClient\Exception\ConnectionException;

class FtpConnectionFactory
{
    /**
     * @throws ConnectionException
     */
    public function create($host = "", $username = "", $password = "", $port = 21, $timeout = 90)
    {
        $connection = new FtpConnection(
            $host ?: HOST,
            $username ?: USERNAME,
            $password ?: PASSWORD,
            $port ?: PORT, 
            $timeout ?: TIMEOUT
        );

        $connection->open();
        
        return $connection;
    }
}
