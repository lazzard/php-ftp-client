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
        $this->assertInstanceOf(FtpCommand::class, $this->getFtpCommandInstance());
    }

    public function testRawWithEmptyStringCommand()
    {
        $this->assertFalse($this->getFtpCommandInstance()->raw(''));
    }

    public function testRawWithSpacesStringCommand()
    {
        $this->assertFalse($this->getFtpCommandInstance()->raw('    '));
    }

    public function testRawWithValidCommand()
    {
        $this->assertInternalType('array', $this->getFtpCommandInstance()->raw('SITE HELP'));
    }

    public function testSiteWithEmptyStringCommand()
    {
        $this->setExpectedException(CommandException::class);
        $this->getFtpCommandInstance()->site('');
    }

    public function testSiteWithSpacesStringCommand()
    {
        $this->setExpectedException(CommandException::class);
        $this->getFtpCommandInstance()->site('    ');
    }

    public function testSiteWithValidCommand()
    {
        $this->assertTrue($this->getFtpCommandInstance()->site('HELP'));
    }

    public function testSiteWithInvalidCommand()
    {
        $this->setExpectedException(CommandException::class);
        $this->getFtpCommandInstance()->site('UNKNOWN');
    }

    public function testExecWithEmptyStringCommand()
    {
        $command = $this->getFtpCommandInstance();

        if (in_array('exec', $command->supportedSiteCommands())) {
            $this->assertFalse($command->exec(''));
        } else {
            $this->setExpectedException(CommandException::class);
            $command->exec('');
        }
    }

    public function testExecWithSpacesStringCommand()
    {
        $command = $this->getFtpCommandInstance();

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
