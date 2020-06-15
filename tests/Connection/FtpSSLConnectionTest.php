<?php

namespace Lazzard\FtpClient\Tests\Connection;

use Lazzard\FtpClient\Connection\FtpSSLConnection;
use Lazzard\FtpClient\Exception\ConnectionException;
use PHPUnit\Framework\TestCase;

class FtpSSLConnectionTest extends TestCase
{
    public function testIfOpensslExtensionNotLoaded()
    {
        if (!extension_loaded('openssl')) {
            $this->setExpectedException(ConnectionException::class);
            new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);
        }
    }

    public function testIfFtpSslConnectFunctionNotExists()
    {
        if (!function_exists('ftp_ssl_connect')) {
            $this->setExpectedException(ConnectionException::class);
            new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);
        }
    }

    public function testIfOpensslLoadedAndFtpSslConnectExists()
    {
        if (extension_loaded('openssl') && function_exists('ftp_ssl_connect')) {
            $this->assertInstanceOf(FtpSSLConnection::class, new FtpSSLConnection(
                HOST,
                USERNAME,
                PASSWORD,
                PORT,
                TIMEOUT
            ));
        } else {
            $this->markTestSkipped();
        }
    }

    /**
     * @depends testIfOpensslLoadedAndFtpSslConnectExists
     */
    public function testCreationConnection()
    {
        $this->assertInstanceOf(FtpSSLConnection::class, new FtpSSLConnection(
            HOST,
            USERNAME,
            PASSWORD,
            PORT,
            TIMEOUT
        ));
    }

    /**
     * @depends testCreationConnection
     */
    public function testOpenConnection()
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);
        $this->assertTrue($connection->open());
    }

    /**
     * @depends testOpenConnection
     */
    public function testCloseConnection()
    {
        $connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);
        $connection->open();
        $this->assertTrue($connection->close());
    }

    /**
     * @depends testOpenConnection
     */
    public function testOpenWithWrongHost()
    {
        $this->setExpectedException(ConnectionException::class);
        (new FtpSSLConnection('foo.website.com', USERNAME, PASSWORD, PORT, TIMEOUT))->open();
    }

    /**
     * @depends testOpenConnection
     */
    public function testOpenWithWrongUsername()
    {
        $this->setExpectedException(ConnectionException::class);
        (new FtpSSLConnection(HOST, 'U&!', PASSWORD, PORT, TIMEOUT))->open();
    }

    /**
     * @depends testOpenConnection
     */
    public function testOpenWithWrongPassword()
    {
        $this->setExpectedException(ConnectionException::class);
        (new FtpSSLConnection(HOST, USERNAME, 'P&!', PORT, TIMEOUT))->open();
    }


    /**
     * @depends testOpenConnection
     */
    public function testOpenWithWrongPort()
    {
        $this->setExpectedException(ConnectionException::class);
        (new FtpSSLConnection(HOST, USERNAME, PASSWORD, -1, TIMEOUT))->open();
    }

    /**
     * @depends testOpenConnection
     */
    public function testOpenWithWrongTimeout()
    {
        $this->setExpectedException(ConnectionException::class);
        (new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, 0))->open();
    }
}
