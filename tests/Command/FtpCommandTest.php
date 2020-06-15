<?php

namespace Lazzard\FtpClient\Tests\Command;

use Lazzard\FtpClient\Command\FtpCommand;
use Lazzard\FtpClient\Exception\CommandException;
use Lazzard\FtpClient\Tests\ConnectionHelper;
use PHPUnit\Framework\TestCase;

class FtpCommandTest extends TestCase
{
    public static function getFtpCommand()
    {
        return new FtpCommand(ConnectionHelper::getConnection());
    }
    
    public function testConstructor()
    {
        $this->assertInstanceOf(FtpCommand::class, self::getFtpCommand());
    }

    /**
     * @depends testConstructor
     */
    public function testRawEmptyStringCommand()
    {
        $this->assertFalse(self::getFtpCommand()->raw(''));
    }

    /**
     * @depends testConstructor
     */
    public function testRawSpacesStringCommand()
    {
        $this->assertFalse(self::getFtpCommand()->raw('    '));
    }

    /**
     * @depends testConstructor
     */
    public function testRawValidCommand()
    {
        $this->assertInternalType('array', self::getFtpCommand()->raw('SITE HELP'));
    }

    /**
     * @depends testConstructor
     */
    public function testSiteEmptyStringCommand()
    {
        $this->setExpectedException(CommandException::class);
        self::getFtpCommand()->site('');
    }

    /**
     * @depends testConstructor
     */
    public function testSiteSpacesStringCommand()
    {
        $this->setExpectedException(CommandException::class);
        self::getFtpCommand()->site('    ');
    }

    /**
     * @depends testConstructor
     */
    public function testSite()
    {
        $this->assertTrue(self::getFtpCommand()->site('HELP'));
    }

    /**
     * @depends testConstructor
     */
    public function testSiteInvalidCommand()
    {
        $this->setExpectedException(CommandException::class);
        self::getFtpCommand()->site('UNKNOWN');
    }

    /**
     * @depends testConstructor
     */
    public function testExecEmptyStringCommand()
    {
        $command = self::getFtpCommand();

        if (in_array('exec', $command->supportedSiteCommands())) {
            $this->assertFalse($command->exec(''));
        } else {
            $this->setExpectedException(CommandException::class);
            $command->exec('');
        }
    }

    /**
     * @depends testConstructor
     */
    public function testExecSpacesStringCommand()
    {
        $command = self::getFtpCommand();

        if (in_array('exec', $command->supportedSiteCommands())) {
            $this->assertFalse($command->exec('    '));
        } else {
            $this->setExpectedException(CommandException::class);
            $this->assertTrue($command->exec('    '));
        }
    }
}
