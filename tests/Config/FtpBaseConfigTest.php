<?php

namespace Lazzard\FtpClient\Tests\Config;

use Lazzard\FtpClient\Config\FtpBaseConfig;
use Lazzard\FtpClient\Exception\ConfigException;
use PHPUnit\Framework\TestCase;

class FtpBaseConfigTest extends TestCase
{
    public function testSetPhpLimitWithValidConfigs()
    {
        try {
            FtpBaseConfig::setPhpLimit([
                "maxExecutionTime" => 0,
                "ignoreUserAbort" => true,
                "memoryLimit" => 512
            ]);
            $this->assertTrue(true);
        } catch (ConfigException $ex) {
            $this->fail();
        }
    }

    public function testSetPhpLimitWithInvalidConfigs()
    {
        $this->setExpectedException(ConfigException::class);
        FtpBaseConfig::setPhpLimit([
            'invalidOption' => null
        ]);
    }

    public function testIsFtpExtensionLoaded()
    {
        if (extension_loaded('ftp')) {
            FtpBaseConfig::isFtpExtensionLoaded();
            $this->assertTrue(true);
        } else {
            $this->setExpectedException('Lazzard\FtpClient\Exception\ConfigException');
            FtpBaseConfig::isFtpExtensionLoaded();
        }
    }
}
