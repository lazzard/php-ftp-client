<?php

namespace Lazzard\FtpClient\Tests\Integration\Command;

use Lazzard\FtpClient\Command\FtpCommand;
use Lazzard\FtpClient\Exception\CommandException;
use Lazzard\FtpClient\Tests\Integration\FtpConnectionFactory;
use PHPUnit\Framework\TestCase;

class FtpCommandTest extends TestCase
{

    public function testRaw(): void
    {
        $factory = new FtpConnectionFactory();
        $command = new FtpCommand($factory->create());

        $this->assertIsArray($command->raw('HELP'));
    }

    public function testSite(): void
    {
        $factory = new FtpConnectionFactory();
        $command = new FtpCommand($factory->create());

        $this->assertTrue($command->site('HELP'));
    }

    public function testExecThrowsExceptionIfTheExecFeatureNotSupported(): void
    {
        $factory = new FtpConnectionFactory();
        $command = new FtpCommand($factory->create());

        if (in_array('EXEC', $command->supportedSiteCommands())) {
            $this->markTestSkipped("SITE EXEC feature already supported.");
        }

        $this->expectException(CommandException::class);
        $this->expectExceptionMessage("SITE EXEC command feature not provided by the FTP server.");

        $command->exec('SITE EXEC test.sh');
    }

    public function testExecIfTheExecFeatureIsSupported(): void
    {
        $factory = new FtpConnectionFactory();
        $command = new FtpCommand($factory->create());

        if (!in_array('EXEC', $command->supportedSiteCommands())) {
            $this->markTestSkipped("SITE EXEC feature is not supported.");
        }

        $this->expectException(CommandException::class);
        $this->expectExceptionMessage("SITE EXEC command feature not provided by the FTP server.");

        $this->assertTrue($command->exec('SITE EXEC test.sh'));
    }

    public function testSupportedSiteCommands(): void
    {
        $factory = new FtpConnectionFactory();
        $command = new FtpCommand($factory->create());

        $this->assertIsArray($command->supportedSiteCommands());
    }
}
