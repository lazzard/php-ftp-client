<?php

namespace Lazzard\FtpClient\Tests\Integration\Command;

use Lazzard\FtpClient\Command\FtpCommand;
use Lazzard\FtpClient\Tests\Integration\ConnectionHelper;
use PHPUnit\Framework\TestCase;

class FtpCommandTest extends TestCase
{    
    public function testConstructor()
    {
        $this->assertInstanceOf(FtpCommand::class, new FtpCommand(ConnectionHelper::getConnection()));
    }

    public function testRaw()
    {
        $command = new FtpCommand(ConnectionHelper::getConnection());

        $this->assertInternalType('array', $command->raw('HELP'));
    }

    public function testSite()
    {
        $command = new FtpCommand(ConnectionHelper::getConnection());
        
        $this->assertTrue($command->site('HELP'));
    }

    public function testExec()
    {
        $command = new FtpCommand(ConnectionHelper::getConnection());
        
        $this->assertTrue($command->exec('SITE EXEC test.sh'));
    }

    public function testSupportedSiteCommands()
    {
        $command = new FtpCommand(ConnectionHelper::getConnection());
        
        $this->assertInternalType('array', $command->supportedSiteCommands());
    }
}
