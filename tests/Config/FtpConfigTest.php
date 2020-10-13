<?php

namespace Lazzard\FtpClient\Tests\Config;

use Lazzard\FtpClient\Config\FtpConfig;
use Lazzard\FtpClient\Exception\ConfigException;
use Lazzard\FtpClient\Tests\ConnectionHelper;

class FtpConfigTest extends \PHPUnit_Framework_TestCase
{
    public function test__constructor()
    {
        $this->assertInstanceOf(FtpConfig::class, self::getFtpConfigInstance());
    }

    public function testSetPassive()
    {
        try {
            $this->assertTrue(self::getFtpConfigInstance()->setPassive(true));
        } catch (ConfigException $ex) {
            $this->fail($ex);
        }
    }

    public function testSetAutoSeek()
    {
        try {
            $this->assertTrue(self::getFtpConfigInstance()->setAutoSeek(false));
        } catch (ConfigException $ex) {
            $this->fail($ex);
        }
    }

    public function testSetTimeout()
    {
        try {
            $this->assertTrue(self::getFtpConfigInstance()->setTimeout(70));
        } catch (ConfigException $ex) {
            $this->fail($ex);
        }
    }

    public function testGetTimeout()
    {
        try {
            $this->assertInternalType('int', self::getFtpConfigInstance()->getTimeout());
        } catch (ConfigException $ex) {
            $this->fail($ex);
        }
    }

    public function testIsAutoSeek()
    {
        $this->assertInternalType('boolean', self::getFtpConfigInstance()->isAutoSeek());
    }

    protected static function getFtpConfigInstance()
    {
        return new FtpConfig(ConnectionHelper::getConnection());
    }
}
