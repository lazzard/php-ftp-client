<?php

namespace Lazzard\FtpClient\Tests\Integration;

use Lazzard\FtpClient\Exception\WrapperException;
use Lazzard\FtpClient\FtpWrapper;
use PHPUnit\Framework\TestCase;

class FtpWrapperTest extends TestCase
{
    public function testGetErrorMessage() : void
    {
        $factory = new FtpConnectionFactory();
        $wrapper = new FtpWrapper($factory->create());
        $wrapper->connect('foo.bar.com');

        $this->assertIsString($wrapper->getErrorMessage());
    }

    public function test__callWithExistingFtpFunction() : void
    {
        $factory = new FtpConnectionFactory();
        $wrapper = new FtpWrapper($factory->create());

        $connection = $wrapper->connect(HOST);

        $this->assertTrue(is_resource($connection) || $connection instanceof \FTP\Connection);
    }

    public function test__callWithNonExistFtpFunction() : void
    {
        $factory = new FtpConnectionFactory();
        $wrapper = new FtpWrapper($factory->create());

        $this->expectException(WrapperException::class);
        $wrapper->function();
    }
}
