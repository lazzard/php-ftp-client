<?php

namespace Lazzard\FtpClient\Tests\Integration\Config;

use Lazzard\FtpClient\Config\FtpConfig;
use Lazzard\FtpClient\Tests\Integration\FtpConnectionFactory;
use PHPUnit\Framework\TestCase;

class FtpConfigTest extends TestCase
{

    public function testSetPassive() : void
    {
        $factory = new FtpConnectionFactory();
        $config = new FtpConfig($factory->create());

        $this->assertTrue($config->setPassive(true));
    }

    public function testSetAutoSeek() : void
    {
        $factory = new FtpConnectionFactory();
        $config = new FtpConfig($factory->create());

        $this->assertTrue($config->setAutoSeek(false));
        $this->assertFalse($config->isAutoSeek());
    }

    public function testSetTimeout() : void
    {
        $factory = new FtpConnectionFactory();
        $config = new FtpConfig($factory->create());

        $this->assertTrue($config->setTimeout(64));
        $this->assertSame(64, $config->getTimeout());
    }

    public function testGetTimeout() : void
    {
        $factory = new FtpConnectionFactory();
        $config = new FtpConfig($factory->create());

        $config->setTimeout(32);
        $this->assertSame(32, $config->getTimeout());
    }

    public function testIsAutoSeek() : void
    {
        $factory = new FtpConnectionFactory();
        $config = new FtpConfig($factory->create());

        $config->setAutoSeek(true);
        $this->assertTrue($config->isAutoSeek());
    }
}
