<?php

namespace Lazzard\FtpClient\Tests\Integration;

use Lazzard\FtpClient\Connection\ConnectionInterface;
use Lazzard\FtpClient\Exception\FtpClientException;
use Lazzard\FtpClient\Exception\WrapperException;
use Lazzard\FtpClient\FtpWrapper;
use PHPUnit\Framework\TestCase;

class FtpWrapperTest extends TestCase
{
    public function testConstruct()
    {
        $wrapper = new FtpWrapper(ConnectionHelper::getConnection());

        $this->assertInstanceOf(FtpWrapper::class, $wrapper);
    }

    public function testGetErrorMessage()
    {
        $wrapper = new FtpWrapper(ConnectionHelper::getConnection());

        $wrapper->connect('foo.bar.com');

        $this->assertInternalType('string', $wrapper->getErrorMessage());
    }

    public function test__callWithExistFtpFunction()
    {
        $wrapper = new FtpWrapper(ConnectionHelper::getConnection());

        $this->assertInternalType('resource', $wrapper->connect(HOST));
    }

    public function test__callWithNonExistFtpFunction()
    {
        $wrapper = new FtpWrapper(ConnectionHelper::getConnection());

        $this->expectException(WrapperException::class);

        $wrapper->function();
    }
}
