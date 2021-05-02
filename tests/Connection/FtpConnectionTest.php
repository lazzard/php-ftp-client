<?php

namespace Lazzard\FtpClient\Tests\Connection;

use Lazzard\FtpClient\Config\FtpConfig;
use Lazzard\FtpClient\Connection\FtpConnection;
use Lazzard\FtpClient\Exception\ConnectionException;
use Lazzard\FtpClient\Exception\FtpClientException;
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
    public function testOpen()
    {
        $this->assertTrue($this->getFtpConnectionInstance()->open());
    }

    /**
     * @depends testOpen
     */
    public function testClose()
    {
        $this->assertTrue($this->getFtpConnectionInstance()->close());
    }

    /**
     * @depends testOpen
     */
    public function testOpenWithWrongHost()
    {
        $this->expectException(ConnectionException::class);
        (new FtpConnection('foo.website.com', USERNAME, PASSWORD, PORT, TIMEOUT))->open();
    }

    /**
     * @depends testOpen
     */
    public function testOpenWithWrongUsername()
    {
        $this->expectException(ConnectionException::class);
        (new FtpConnection(HOST, 'U&!', PASSWORD, PORT, TIMEOUT))->open();
    }

    /**
     * @depends testOpen
     */
    public function testOpenWithWrongPassword()
    {
        $this->expectException(ConnectionException::class);
        (new FtpConnection(HOST, USERNAME, 'P&!', PORT, TIMEOUT))->open();
    }

    /**
     * @depends testOpen
     */
    public function testOpenWithWrongPort()
    {
        $this->expectException(ConnectionException::class);
        (new FtpConnection(HOST, USERNAME, PASSWORD, -1, TIMEOUT))->open();
    }

    /**
     * @depends testOpen
     */
    public function testOpenWithWrongTimeout()
    {
        $this->expectException(ConnectionException::class);
        (new FtpConnection(HOST, USERNAME, PASSWORD, PORT, 0))->open();
    }

    /**
     * @depends testOpen
     */
    public function testIsPassiveWithPassiveMode()
    {
        $ftpConfig = new FtpConfig($this->getFtpConnectionInstance());
        try {
            $ftpConfig->setPassive(true);
            self::assertTrue($this->getFtpConnectionInstance()->isPassive());
        } catch (FtpClientException $e) {
            self::markTestSkipped();
        }
    }

    /**
     * @depends testOpen
     */
    public function testIsPassiveWithActiveMode()
    {
        $ftpConfig = new FtpConfig($this->getFtpConnectionInstance());
        try {
            $ftpConfig->setPassive(false);
            self::assertFalse($this->getFtpConnectionInstance()->isPassive());
        } catch (FtpClientException $e) {
            self::markTestSkipped();
        }
    }

    protected function getFtpConnectionInstance()
    {
        if (!self::$connection) {
            self::$connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);
        }

        return self::$connection;
    }
}
