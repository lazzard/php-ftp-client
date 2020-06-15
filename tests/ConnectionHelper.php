<?php

namespace Lazzard\FtpClient\Tests;

use Lazzard\FtpClient\Connection\ConnectionInterface;
use Lazzard\FtpClient\Connection\FtpConnection;
use Lazzard\FtpClient\Connection\FtpSSLConnection;

/**
 * Singleton class to avoid multiple FTP connections for each depending module.
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

    public static function open()
    {
        if (!USESSL) {
            self::$connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);
        } else {
            self::$connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);
        }

        self::$connection->open();
    }
}
