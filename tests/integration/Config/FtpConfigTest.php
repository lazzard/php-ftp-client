<?php

namespace Lazzard\FtpClient\Tests\Integration\Config;

use Lazzard\FtpClient\Config\FtpConfig;
use Lazzard\FtpClient\Tests\Integration\ConnectionHelper;
use PHPUnit\Framework\TestCase;

class FtpConfigTest extends TestCase
{
    public function testConstructor()
    {
        $this->assertInstanceOf(FtpConfig::class, new FtpConfig(ConnectionHelper::getConnection()));
    }

    public function testSetPassive()
    {
        $config = new FtpConfig(ConnectionHelper::getConnection());

        $this->assertTrue($config->setPassive(true));
    }

    public function testSetAutoSeek()
    {
        $config = new FtpConfig(ConnectionHelper::getConnection());

        $this->assertTrue($config->setAutoSeek(false));
        $this->assertFalse($config->isAutoSeek());
    }

    public function testSetTimeout()
    {
        $config = new FtpConfig(ConnectionHelper::getConnection());

        $this->assertTrue($config->setTimeout(64));
        $this->assertSame(64, $config->getTimeout());
    }

    public function testGetTimeout()
    {
        $config = new FtpConfig(ConnectionHelper::getConnection());

        $config->setTimeout(32);
        $this->assertSame(32, $config->getTimeout());
    }

    public function testIsAutoSeek()
    {
        $config = new FtpConfig(ConnectionHelper::getConnection());

        $config->setAutoSeek(true);
        $this->assertTrue($config->isAutoSeek());
    }
}