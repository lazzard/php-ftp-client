<?php

namespace Lazzard\FtpClient\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Lazzard\FtpClient\Exception\WrapperException;
use Lazzard\FtpClient\FtpWrapper;

class FtpWrapperTest extends TestCase
{
    public function testConstruct() : void
    {
        $wrapper = new FtpWrapper(ConnectionHelper::getConnection());

        $this->assertInstanceOf(FtpWrapper::class, $wrapper);
    }

    public function testGetErrorMessage() : void
    {
        $wrapper = new FtpWrapper(ConnectionHelper::getConnection());

        $wrapper->connect('foo.bar.com');

        $this->assertIsString($wrapper->getErrorMessage());
    }

    public function test__callWithExistFtpFunction() : void
    {
        $wrapper = new FtpWrapper(ConnectionHelper::getConnection());

        $this->assertIsResource($wrapper->connect(HOST));
    }

    public function test__callWithNonExistFtpFunction() : void
    {
        $wrapper = new FtpWrapper(ConnectionHelper::getConnection());

        $this->expectException(WrapperException::class);

        $wrapper->function();
    }
}
