<?php

namespace Lazzard\FtpClient\Tests\Config;

use Lazzard\FtpClient\Config\FtpConfig;
use Lazzard\FtpClient\Exception\ConfigException;
use Lazzard\FtpClient\Tests\ConnectionHelper;

class FtpConfigTest extends \PHPUnit_Framework_TestCase
{
    public static function getFtpConfig()
    {
        return new FtpConfig(ConnectionHelper::getConnection());
    }

    public function testConstructorWithoutOptions()
    {
        $this->assertInstanceOf(FtpConfig::class, new FtpConfig(ConnectionHelper::getConnection()));
    }

    public function testConstructorWithValidOptions()
    {
        (new FtpConfig(ConnectionHelper::getConnection(), [
            "passive" => true,
            "autoSeek" => false,
            "timeout" => 90,
            "initialDirectory" => "public_html"
        ]));
        $this->assertTrue(true);
    }

    public function testConstructorWithInvalidOptions()
    {
        $this->setExpectedException(ConfigException::class);
        (new FtpConfig(ConnectionHelper::getConnection(), [
            "invalidOption" => "value"
        ]));
    }

    public function testApplyValidOptions()
    {
        try {
            (new FtpConfig(ConnectionHelper::getConnection(), [
                "passive" => true,
                "autoSeek" => false,
                "timeout" => 90,
                "initialDirectory" => "public_html"
            ]))->apply();
            $this->assertTrue(true);
        } catch (ConfigException $ex) {
            $this->fail($ex);
        }
    }

    public function testApplyInvalidOptions()
    {
        $this->setExpectedException(ConfigException::class);
        (new FtpConfig(ConnectionHelper::getConnection(), [
            "active" => true,
        ]))->apply();
    }

    public function testSetPassive()
    {
        try {
            $this->assertTrue((new FtpConfig(ConnectionHelper::getConnection()))->setPassive(true));
        } catch (ConfigException $ex) {
            $this->fail($ex);
        }
    }

    public function testSetAutoSeek()
    {
        try {
            $this->assertTrue((new FtpConfig(ConnectionHelper::getConnection()))->setAutoSeek(false));
        } catch (ConfigException $ex) {
            $this->fail($ex);
        }
    }

    public function testSetTimeout()
    {
        try {
            $this->assertTrue((new FtpConfig(ConnectionHelper::getConnection()))->setTimeout(70));
        } catch (ConfigException $ex) {
            $this->fail($ex);
        }
    }

    public function testGetTimeout()
    {
        try {
            $this->assertInternalType('int', (new FtpConfig(ConnectionHelper::getConnection()))->getTimeout());
        } catch (ConfigException $ex) {
            $this->fail($ex);
        }
    }

    public function testIsAutoSeek()
    {
        $this->assertInternalType('boolean', (new FtpConfig(ConnectionHelper::getConnection()))->isAutoSeek());
    }

    public function testGetConfig()
    {
        $this->assertInternalType('array', (new FtpConfig(ConnectionHelper::getConnection()))->getConfig());
    }
}
