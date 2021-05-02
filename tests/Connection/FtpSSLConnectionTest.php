<?php

namespace Lazzard\FtpClient\Tests\Connection;

use Lazzard\FtpClient\Connection\FtpSSLConnection;
use Lazzard\FtpClient\Exception\ConnectionException;
use PHPUnit\Framework\TestCase;

class FtpSSLConnectionTest extends TestCase
{
    /** @var FtpSSLConnection */
    protected static $connection;

    public function testIfOpensslLoadedAndFtpSslConnectFunctionExists()
    {
        if (extension_loaded('openssl') && function_exists('ftp_ssl_connect')) {
            $this->assertInstanceOf(FtpSSLConnection::class, $this->getFtpSSLConnectionInstance());
        } else {
            $this->markTestSkipped();
        }
    }

    public function testIfOpensslLoadedOrFtpSslConnectFunctionNotExists()
    {
        if (!extension_loaded('openssl') || !function_exists('ftp_ssl_connect')) {
            $this->expectException(ConnectionException::class);
            $this->getFtpSSLConnectionInstance();
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
    public function testOpen()
    {
        $this->assertTrue($this->getFtpSSLConnectionInstance()->open());
    }

    /**
     * @depends testOpen
     */
    public function testClose()
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
