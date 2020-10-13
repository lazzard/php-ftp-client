<?php

namespace Lazzard\FtpClient\Tests;

use Lazzard\FtpClient\Connection\ConnectionInterface;
use Lazzard\FtpClient\Connection\FtpConnection;
use Lazzard\FtpClient\Connection\FtpSSLConnection;

/**
 * A singleton class to avoid multiple FTP connections for each depending module.
 */
class ConnectionHelper
{
    /** @var ConnectionInterface */
    protected static $connection;

    public static function getConnection()
    {
        if (!self::$connection) {
            self::open();
        }

        return self::$connection;
    }

    protected static function open()
    {
        $class = USESSL ? FtpSSLConnection::class : FtpConnection::class;
        $reflection = new \ReflectionClass($class);
        self::$connection = $reflection->newInstanceArgs([HOST, USERNAME, PASSWORD, PORT, TIMEOUT]);
        self::$connection->open();
    }
}
