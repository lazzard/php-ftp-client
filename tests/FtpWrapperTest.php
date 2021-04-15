<?php

namespace Lazzard\FtpClient\Tests;

use Lazzard\FtpClient\Connection\ConnectionInterface;
use Lazzard\FtpClient\Exception\FtpClientException;
use Lazzard\FtpClient\FtpWrapper;
use PHPUnit\Framework\TestCase;

class FtpWrapperTest extends TestCase
{
    public function test__construct()
    {
        $this->assertInstanceOf(FtpWrapper::class, $this->getFtpWrapperInstance());
    }

    public function testGetFtpErrorMessage()
    {
        $wrapper = $this->getFtpWrapperInstance();
        $wrapper->chdir('foo/bar');
        $this->assertInternalType('string', $wrapper->getErrorMessage());
    }

    public function testGetConnection()
    {
        $this->assertInstanceOf(ConnectionInterface::class, $this->getFtpWrapperInstance()->getConnection());
    }

    public function testSetConnection()
    {
        $this->assertNull($this->getFtpWrapperInstance()->setConnection(ConnectionHelper::getConnection()));
    }

    public function test__callWithInvalidFtpFunction()
    {
        $this->setExpectedException(FtpClientException::class);
        $this->getFtpWrapperInstance()->ftp('foo@bar.com');
    }

    public function test__callWithAValidFtpFunction()
    {
        $this->assertInternalType('resource', $this->getFtpWrapperInstance()->connect(HOST));
    }

    protected function getFtpWrapperInstance()
    {
        return new FtpWrapper(ConnectionHelper::getConnection());
    }
}
