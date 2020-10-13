<?php

namespace Lazzard\FtpClient\Tests\Command;

use Lazzard\FtpClient\Command\FtpCommand;
use Lazzard\FtpClient\Exception\CommandException;
use Lazzard\FtpClient\Tests\ConnectionHelper;
use PHPUnit\Framework\TestCase;

class FtpCommandTest extends TestCase
{    
    public function test__constructor()
    {
        $this->assertInstanceOf(FtpCommand::class, self::getFtpCommandInstance());
    }

    public function testRawWithEmptyStringCommand()
    {
        $this->assertFalse(self::getFtpCommandInstance()->raw(''));
    }

    public function testRawWithSpacesStringCommand()
    {
        $this->assertFalse(self::getFtpCommandInstance()->raw('    '));
    }

    public function testRawWithValidCommand()
    {
        $this->assertInternalType('array', self::getFtpCommandInstance()->raw('SITE HELP'));
    }

    public function testSiteWithEmptyStringCommand()
    {
        $this->setExpectedException(CommandException::class);
        self::getFtpCommandInstance()->site('');
    }

    public function testSiteWithSpacesStringCommand()
    {
        $this->setExpectedException(CommandException::class);
        self::getFtpCommandInstance()->site('    ');
    }

    public function testSiteWithValidCommand()
    {
        $this->assertTrue(self::getFtpCommandInstance()->site('HELP'));
    }

    public function testSiteWithInvalidCommand()
    {
        $this->setExpectedException(CommandException::class);
        self::getFtpCommandInstance()->site('UNKNOWN');
    }

    public function testExecWithEmptyStringCommand()
    {
        $command = self::getFtpCommandInstance();

        if (in_array('exec', $command->supportedSiteCommands())) {
            $this->assertFalse($command->exec(''));
        } else {
            $this->setExpectedException(CommandException::class);
            $command->exec('');
        }
    }

    public function testExecWithSpacesStringCommand()
    {
        $command = self::getFtpCommandInstance();

        if (in_array('exec', $command->supportedSiteCommands())) {
            $this->assertFalse($command->exec('    '));
        } else {
            $this->setExpectedException(CommandException::class);
            $this->assertTrue($command->exec('    '));
        }
    }

    protected function getFtpCommandInstance()
    {
        return new FtpCommand(ConnectionHelper::getConnection());
    }
}
