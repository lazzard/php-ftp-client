<?php

namespace Lazzard\FtpClient\Tests\Integration\Command;

use PHPUnit\Framework\TestCase;
use Lazzard\FtpClient\Command\FtpCommand;
use Lazzard\FtpClient\Tests\Integration\ConnectionHelper;
use Lazzard\FtpClient\Exception\CommandException;

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

    public function testExecThrowsExceptionIfTheExecFeatureNotSupported() : void
    {
        $command = new FtpCommand(ConnectionHelper::getConnection());
        
        if (in_array('EXEC', $command->supportedSiteCommands())) {
            $this->markTestSkipped("SITE EXEC feature already supported.");
        }

        $this->expectException(CommandException::class);
        $this->expectExceptionMessage("SITE EXEC command feature not provided by the FTP server.");

        $command->exec('SITE EXEC test.sh');
    }

    public function testExecIfTheExecFeatureIsSupported() : void
    {
        $command = new FtpCommand(ConnectionHelper::getConnection());
        
        if (!in_array('EXEC', $command->supportedSiteCommands())) {
            $this->markTestSkipped("SITE EXEC feature is not supported.");
        }

        $this->expectException(CommandException::class);
        $this->expectExceptionMessage("SITE EXEC command feature not provided by the FTP server.");
        
        $this->assertTrue($command->exec('SITE EXEC test.sh'));
    }

    public function testSupportedSiteCommands() : void
    {
        $command = new FtpCommand(ConnectionHelper::getConnection());
        
        $this->assertIsArray($command->supportedSiteCommands());
    }
}
