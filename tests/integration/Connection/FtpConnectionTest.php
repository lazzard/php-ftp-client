<?php

namespace Lazzard\FtpClient\Tests\Integration\Connection;

use PHPUnit\Framework\TestCase;
use Lazzard\FtpClient\Config\FtpConfig;
use Lazzard\FtpClient\Connection\ConnectionInterface;
use Lazzard\FtpClient\Connection\FtpConnection;
use Lazzard\FtpClient\Exception\ConnectionException;

class FtpConnectionTest extends TestCase
{
    public function testConstructor() : void
    {
        $this->assertInstanceOf(ConnectionInterface::class, new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT));
    }

    public function testGetStreamWithOpenedConnection() : void
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $connection->open();

        $this->assertSame('resource', gettype($connection->getStream()));

        $connection->close();
    }

    public function testGetStreamWithClosedConnection() : void
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->expectException(ConnectionException::class);

        $connection->getStream();
    }

    public function testGetHost() : void
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(HOST, $connection->getHost());
    }

    public function testGetPort() : void
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(PORT, $connection->getPort());
    }

    public function testGetTimeout() : void
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(TIMEOUT, $connection->getTimeout());
    }

    public function testGetUsername() : void
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(USERNAME, $connection->getUsername());
    }

    public function testGetPassword() : void
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(PASSWORD, $connection->getPassword());
    }

    public function testIsConnectedWithOpenedConnection() : void
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $connection->open();

        $this->assertTrue($connection->isConnected());

        $connection->close();
    }

    public function testIsConnectedWithClosedConnection() : void
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertFalse($connection->isConnected());
    }

    public function testOpen() : void
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertTrue($connection->open());

        $connection->close();
    }

    public function testClose() : void
    {
        $connection = new FtpConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $connection->open();

        $this->assertTrue($connection->close());
    }
}
