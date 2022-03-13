<?php

namespace Lazzard\FtpClient\Tests\Integration\Connection;

use Lazzard\FtpClient\Connection\FtpSSLConnection;
use Lazzard\FtpClient\Exception\ConnectionException;
use PHPUnit\Framework\TestCase;

class FtpSSLConnectionTest extends TestCase
{

    public function testGetStreamWithOpenedConnection() : void
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $connection->open();

        $this->assertTrue(is_resource($connection->getStream()) || $connection->getStream() instanceof \FTP\Connection);

        $connection->close();
    }

    public function testGetStreamWithClosedConnection() : void
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->expectException(ConnectionException::class);

        $connection->getStream();
    }

    public function testGetHost() : void
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(HOST, $connection->getHost());
    }

    public function testGetPort() : void
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(PORT, $connection->getPort());
    }

    public function testGetTimeout() : void
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(TIMEOUT, $connection->getTimeout());
    }

    public function testGetUsername() : void
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(USERNAME, $connection->getUsername());
    }

    public function testGetPassword() : void
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(PASSWORD, $connection->getPassword());
    }

    public function testIsConnectedWithOpenedConnection() : void
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $connection->open();

        $this->assertTrue($connection->isConnected());

        $connection->close();
    }

    public function testIsConnectedWithClosedConnection() : void
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertFalse($connection->isConnected());
    }

    public function testOpen() : void
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertTrue($connection->open());

        $connection->close();
    }

    public function testClose() : void
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $connection->open();

        $this->assertTrue($connection->close());
    }
}
