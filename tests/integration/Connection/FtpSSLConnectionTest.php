<?php

namespace Lazzard\FtpClient\Tests\Integration\Connection;

use PHPUnit\Framework\TestCase;
use Lazzard\FtpClient\Config\FtpConfig;
use Lazzard\FtpClient\Connection\ConnectionInterface;
use Lazzard\FtpClient\Connection\FtpSSLConnection;
use Lazzard\FtpClient\Exception\ConnectionException;

class FtpSSLConnectionTest extends TestCase
{
    public function testConstructor()
    {
        $this->assertInstanceOf(ConnectionInterface::class, new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT));
    }

    /**
     * @depends testConstructor
     */
    public function testGetStreamWithOpenedConnection()
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $connection->open();

        $this->assertSame('resource', gettype($connection->getStream()));

        $connection->close();
    }

    /**
     * @depends testConstructor
     */
    public function testGetStreamWithClosedConnection()
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->expectException(ConnectionException::class);

        $connection->getStream();
    }

    /**
     * @depends testConstructor
     */
    public function testGetHost()
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(HOST, $connection->getHost());
    }

    /**
     * @depends testConstructor
     */
    public function testGetPort()
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(PORT, $connection->getPort());
    }

    /**
     * @depends testConstructor
     */
    public function testGetTimeout()
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(TIMEOUT, $connection->getTimeout());
    }

    /**
     * @depends testConstructor
     */
    public function testGetUsername()
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(USERNAME, $connection->getUsername());
    }

    /**
     * @depends testConstructor
     */
    public function testGetPassword()
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(PASSWORD, $connection->getPassword());
    }

    /**
     * @depends testConstructor
     */
    public function testIsConnectedWithOpenedConnection()
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $connection->open();

        $this->assertTrue($connection->isConnected());

        $connection->close();
    }

    /**
     * @depends testConstructor
     */
    public function testIsConnectedWithClosedConnection()
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertFalse($connection->isConnected());
    }

    /**
     * @depends testConstructor
     */
    public function testIsPassiveWithPassiveMode()
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $connection->open();

        $ftpConfig = new FtpConfig($connection);

        $ftpConfig->setPassive(true);

        $this->assertTrue($connection->isPassive());

        $connection->close();
    }

    /**
     * @depends testConstructor
     */
    public function testIsPassiveWithActiveMode()
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $connection->open();

        $ftpConfig = new FtpConfig($connection);

        $ftpConfig->setPassive(false);

        $this->assertFalse($connection->isPassive());

        $connection->close();
    }

    /**
     * @depends testConstructor
     */
    public function testOpen()
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertTrue($connection->open());

        $connection->close();
    }

    /**
     * @depends testConstructor
     */
    public function testClose()
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $connection->open();

        $this->assertTrue($connection->close());
    }
}
