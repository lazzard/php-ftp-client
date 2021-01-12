<?php

namespace Lazzard\FtpClient\Tests\Connection;

use Lazzard\FtpClient\Connection\FtpConnection;
use Lazzard\FtpClient\Exception\ConnectionException;
use PHPUnit\Framework\TestCase;

class FtpConnectionTest extends TestCase
{
    /** @var FtpConnection */
    protected static $connection;

    public function test__constructor()
    {
        $this->assertInstanceOf(FtpConnection::class, $this->getFtpConnectionInstance());
    }

    /**
     * @depends test__constructor
     */
    public function testOpenConnection()
    {
        $this->assertTrue($this->getFtpConnectionInstance()->open());
    }

    /**
     * @depends testOpenConnection
     */
    public function testCloseConnection()
    {
        $this->assertTrue($this->getFtpConnectionInstance()->close());
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

    protected function getFtpConnectionInstance()
    {
        if (!self::$connection) {
            self::$connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);
        }

        return self::$connection;
    }
}
