<?php

namespace Lazzard\FtpClient\Tests\Connection;

use Lazzard\FtpClient\Connection\FtpSSLConnection;
use Lazzard\FtpClient\Exception\ConnectionException;
use PHPUnit\Framework\TestCase;

class FtpSSLConnectionTest extends TestCase
{
    /** @var FtpSSLConnection */
    protected static $connection;

    public function testIfOpensslExtensionNotLoaded()
    {
        if (!extension_loaded('openssl')) {
            $this->setExpectedException(ConnectionException::class);
            $this->getFtpSSLConnectionInstance();
        }
    }

    public function testIfFtpSslConnectFunctionNotExists()
    {
        if (!function_exists('ftp_ssl_connect')) {
            $this->setExpectedException(ConnectionException::class);
            $this->getFtpSSLConnectionInstance();
        }
    }

    public function testIfOpensslLoadedAndFtpSslConnectFunctionExists()
    {
        if (extension_loaded('openssl') && function_exists('ftp_ssl_connect')) {
            $this->assertInstanceOf(FtpSSLConnection::class, $this->getFtpSSLConnectionInstance());
        } else {
            $this->markTestSkipped();
        }
    }

    /**
     * @depends testIfOpensslLoadedAndFtpSslConnectFunctionExists
     */
    public function test_constructor()
    {
        $this->assertInstanceOf(FtpSSLConnection::class, $this->getFtpSSLConnectionInstance());
    }

    /**
     * @depends testConstructor
     */
    public function testOpenConnection()
    {
        $this->assertTrue($this->getFtpSSLConnectionInstance()->open());
    }

    /**
     * @depends testOpenConnection
     */
    public function testCloseConnection()
    {
        $this->assertTrue($this->getFtpSSLConnectionInstance()->close());
    }

    protected function getFtpSSLConnectionInstance()
    {
        if (!self::$connection) {
            self::$connection = new FtpSSLConnection(HOST, USERNAME, PASSWORD, PORT, TIMEOUT);
        }

        return self::$connection;
    }
}
