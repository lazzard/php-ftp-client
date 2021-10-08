<?php

namespace Lazzard\FtpClient\Tests\Integration\Command;

use Lazzard\FtpClient\Command\FtpCommand;
use Lazzard\FtpClient\Tests\Integration\ConnectionHelper;
use PHPUnit\Framework\TestCase;

class FtpCommandTest extends TestCase
{    
    public function testConstructor() : void
    {
        $this->assertInstanceOf(FtpCommand::class, new FtpCommand(ConnectionHelper::getConnection()));
    }

    public function testRaw() : void
    {
        $command = new FtpCommand(ConnectionHelper::getConnection());

        $this->assertIsArray($command->raw('HELP'));
    }

    public function testSite() : void
    {
        $command = new FtpCommand(ConnectionHelper::getConnection());
        
        $this->assertTrue($command->site('HELP'));
    }

    public function testExec() : void
    {
        $command = new FtpCommand(ConnectionHelper::getConnection());
        
        $this->assertTrue($command->exec('SITE EXEC test.sh'));
    }

    public function testSupportedSiteCommands() : void
    {
        $command = new FtpCommand(ConnectionHelper::getConnection());
        
        $this->assertIsArray($command->supportedSiteCommands());
    }
}
