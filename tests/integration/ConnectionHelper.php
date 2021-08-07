<?php

namespace Lazzard\FtpClient\Tests\Integration;

use Lazzard\FtpClient\Config\FtpConfig;
use Lazzard\FtpClient\Connection\ConnectionInterface;
use Lazzard\FtpClient\Connection\FtpConnection;

/**
 * Avoids multiple FTP connection creating.
 */
class ConnectionHelper
{
    /** @var ConnectionInterface */
    protected static $connection;

    /**
     * @return ConnectionInterface
     */
    public static function getConnection()
    {
        if (!self::$connection) {
            self::open(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);
            self::passive(PASSIVE);
        }

        return self::$connection;
    }

    protected static function open()
    {
        self::$connection = new FtpConnection(...func_get_args());
        self::$connection->open();
    }

    protected static function passive($passive) 
    {
        (new FtpConfig(self::$connection))->setPassive($passive);
    }
}
