<?php

namespace Lazzard\FtpClient\Tests\Integration\Connection;

use PHPUnit\Framework\TestCase;
use Lazzard\FtpClient\Connection\ConnectionInterface;
use Lazzard\FtpClient\Connection\FtpSSLConnection;
use Lazzard\FtpClient\Exception\ConnectionException;

class FtpSSLConnectionTest extends TestCase
{
    public function testConstructor() : void
    {
        $this->assertInstanceOf(ConnectionInterface::class, new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT));
    }

    /**
     * @depends testConstructor
     */
    public function testGetStreamWithOpenedConnection() : void
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $connection->open();

        $this->assertSame('resource', gettype($connection->getStream()));

        $connection->close();
    }

    /**
     * @depends testConstructor
     */
    public function testGetStreamWithClosedConnection() : void
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->expectException(ConnectionException::class);

        $connection->getStream();
    }

    /**
     * @depends testConstructor
     */
    public function testGetHost() : void
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(HOST, $connection->getHost());
    }

    /**
     * @depends testConstructor
     */
    public function testGetPort() : void
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(PORT, $connection->getPort());
    }

    /**
     * @depends testConstructor
     */
    public function testGetTimeout() : void
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(TIMEOUT, $connection->getTimeout());
    }

    /**
     * @depends testConstructor
     */
    public function testGetUsername() : void
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(USERNAME, $connection->getUsername());
    }

    /**
     * @depends testConstructor
     */
    public function testGetPassword() : void
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertSame(PASSWORD, $connection->getPassword());
    }

    /**
     * @depends testConstructor
     */
    public function testIsConnectedWithOpenedConnection() : void
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $connection->open();

        $this->assertTrue($connection->isConnected());

        $connection->close();
    }

    /**
     * @depends testConstructor
     */
    public function testIsConnectedWithClosedConnection() : void
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertFalse($connection->isConnected());
    }

    /**
     * @depends testConstructor
     */
    public function testOpen() : void
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $this->assertTrue($connection->open());

        $connection->close();
    }

    /**
     * @depends testConstructor
     */
    public function testClose() : void
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);

        $connection->open();

        $this->assertTrue($connection->close());
    }
}
