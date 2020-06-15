<?php

namespace Lazzard\FtpClient\Tests\Connection;

use Lazzard\FtpClient\Connection\FtpConnection;
use Lazzard\FtpClient\Exception\ConnectionException;
use PHPUnit\Framework\TestCase;

class FtpConnectionTest extends TestCase
{
    /** @var FtpConnection */
    protected static $connection;

    public static function getFtpConnection()
    {
        if (!self::$connection) {
            return self::$connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);
        }

        return self::$connection;
    }

    public function testCreationConnection()
    {
        $this->assertInstanceOf(FtpConnection::class, self::getFtpConnection());
    }

    /**
     * @depends testCreationConnection
     */
    public function testOpenConnection()
    {
        $this->assertTrue(self::getFtpConnection()->open());
    }

    /**
     * @depends testOpenConnection
     */
    public function testCloseConnection()
    {
        $this->assertTrue(self::getFtpConnection()->close());
    }

    /**
     * @depends testOpenConnection
     */
    public function testOpenWithWrongHost()
    {
        $this->setExpectedException(ConnectionException::class);
        (new FtpConnection('foo.website.com', USERNAME, PASSWORD, PORT, TIMEOUT))->open();
    }

    /**
     * @depends testOpenConnection
     */
    public function testOpenWithWrongUsername()
    {
        $this->setExpectedException(ConnectionException::class);
        (new FtpConnection(HOST, 'U&!', PASSWORD, PORT, TIMEOUT))->open();
    }

    /**
     * @depends testOpenConnection
     */
    public function testOpenWithWrongPassword()
    {
        $this->setExpectedException(ConnectionException::class);
        (new FtpConnection(HOST, USERNAME, 'P&!', PORT, TIMEOUT))->open();
    }

    /**
     * @depends testOpenConnection
     */
    public function testOpenWithWrongPort()
    {
        $this->setExpectedException(ConnectionException::class);
        (new FtpConnection(HOST, USERNAME, PASSWORD, -1, TIMEOUT))->open();
    }

    /**
     * @depends testOpenConnection
     */
    public function testOpenWithWrongTimeout()
    {
        $this->setExpectedException(ConnectionException::class);
        (new FtpConnection(HOST, USERNAME, PASSWORD, PORT, 0))->open();
    }
}
