<?php

namespace Lazzard\FtpClient\Tests\Integration\Connection;

use Lazzard\FtpClient\Config\FtpConfig;
use Lazzard\FtpClient\Connection\ConnectionInterface;
use Lazzard\FtpClient\Connection\FtpConnection;
use Lazzard\FtpClient\Exception\ConnectionException;

class FtpConnectionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $this->assertInstanceOf(ConnectionInterface::class, new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT));
    }

    public function testGetStreamWithOpenedConnection()
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $connection->open();

        $this->assertSame('resource', gettype($connection->getStream()));

        $connection->close();
    }

    public function testGetStreamWithClosedConnection()
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->expectException(ConnectionException::class);

        $connection->getStream();
    }

    public function testGetHost()
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(HOST, $connection->getHost());
    }

    public function testGetPort()
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(PORT, $connection->getPort());
    }

    public function testGetTimeout()
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(TIMEOUT, $connection->getTimeout());
    }

    public function testGetUsername()
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(USERNAME, $connection->getUsername());
    }

    public function testGetPassword()
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(PASSWORD, $connection->getPassword());
    }

    public function testIsConnectedWithOpenedConnection()
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $connection->open();

        $this->assertTrue($connection->isConnected());

        $connection->close();
    }

    public function testIsConnectedWithClosedConnection()
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertFalse($connection->isConnected());
    }

    public function testIsPassiveWithPassiveMode()
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $connection->open();

        $ftpConfig = new FtpConfig($connection);

        $ftpConfig->setPassive(true);

        $this->assertTrue($connection->isPassive());

        $connection->close();
    }

    public function testIsPassiveWithActiveMode()
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $connection->open();

        $ftpConfig = new FtpConfig($connection);

        $ftpConfig->setPassive(false);

        $this->assertFalse($connection->isPassive());

        $connection->close();
    }

    public function testOpen()
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertTrue($connection->open());

        $connection->close();
    }

    public function testClose()
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $connection->open();

        $this->assertTrue($connection->close());
    }
}
