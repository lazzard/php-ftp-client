<?php

namespace Lazzard\FtpClient\Tests;

use Lazzard\FtpClient\Connection\ConnectionInterface;
use Lazzard\FtpClient\Exception\FtpClientException;
use Lazzard\FtpClient\FtpClient;
use Lazzard\FtpClient\FtpWrapper;

class FtpClientTest extends \PHPUnit_Framework_TestCase
{
    protected $testFile = 'lazzard_ftp_client_test_file.txt';
    protected $testDir  = 'lazzard_ftp_client_test_directory';

    public function test__constructor()
    {
        $this->assertInstanceOf(FtpClient::class, $this->getFtpClientInstance());
    }

    public function testGetParent()
    {
        $this->assertInternalType('string', $this->getFtpClientInstance()->getParent());
    }

    public function testCreateFileWithoutContent()
    {
        $this->assertTrue($this->getFtpClientInstance()->createFile($this->testFile));
    }

    public function testCreateFileWithContent()
    {
        $this->assertTrue($this->getFtpClientInstance()->createFile($this->testFile, "content ...!"));
    }

    public function testCreateDirectory()
    {
        $this->assertTrue($this->getFtpClientInstance()->createDirectory($this->testDir));
    }

    public function testFileSize()
    {
        $this->assertInternalType('int', $this->getFtpClientInstance()->fileSize($this->testFile));
    }

    public function testDirSize()
    {
        $this->assertInternalType('int', $this->getFtpClientInstance()->dirSize($this->testDir));
    }

    public function testDownload()
    {
        $localFile = tempnam(sys_get_temp_dir(), 'test.txt');
        $this->assertTrue($this->getFtpClientInstance()->download($this->testFile, $localFile));
    }

    public function testAsyncDownload()
    {
        $localFile = tempnam(sys_get_temp_dir(), 'test2.txt');
        $this->assertTrue($this->getFtpClientInstance()->asyncDownload($this->testFile, $localFile, function (){
            // Do something
        }));
    }

    public function testMove()
    {
        $testFile = 'test_move.txt';
        $this->getFtpClientInstance()->createFile($testFile);
        $this->assertTrue($this->getFtpClientInstance()->move($testFile, $this->testDir));
        $this->getFtpClientInstance()->removeFile("$this->testDir/$testFile");
    }

    public function testSetPermissionsFailure()
    {
        $wrapper = $this->getMockBuilder(FtpWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $wrapper->expects(self::once())
            ->method('__call')
            ->with('chmod')
            ->willReturn(false);

        $ftp = $this->getFtpClientInstance();
        $ftp->setWrapper($wrapper);

        $this->setExpectedException(FtpClientException::class);
        $ftp->setPermissions('file.txt', 744);
    }

    public function testSetPermissionsWithArrayParameter()
    {
        $this->assertTrue($this->getFtpClientInstance()->setPermissions($this->testFile, [
            'owner' => 'r-w',
            'group' => 'e',
            'other' => 'w-r'
        ]));
    }

    public function testSetPermissionsWithNumericParameter()
    {
        $this->assertTrue($this->getFtpClientInstance()->setPermissions($this->testFile, 777));
    }

    public function testIsEmptyWithAnEmptyFile()
    {
        $this->getFtpClientInstance()->createFile($this->testFile);
        $this->assertTrue($this->getFtpClientInstance()->isEmpty($this->testFile));
    }

    public function testIsEmptyWithANonEmptyFile()
    {
        $this->getFtpClientInstance()->createFile($this->testFile, 'content ...!');
        $this->assertFalse($this->getFtpClientInstance()->isEmpty($this->testFile));
    }

    public function testIsEmptyWithAnEmptyDirectory()
    {
        $this->assertTrue($this->getFtpClientInstance()->isEmpty($this->testDir));
    }

    public function testIsEmptyWithANonEmptyDirectory()
    {
        $this->getFtpClientInstance()->createDirectory($this->testDir);
        $this->getFtpClientInstance()->createFile("$this->testDir/$this->testFile");
        $this->assertFalse($this->getFtpClientInstance()->isEmpty($this->testDir));
    }

    public function testIsFile()
    {
        $this->assertTrue($this->getFtpClientInstance()->isFile($this->testFile));
    }

    public function testIsDir()
    {
        $this->assertTrue($this->getFtpClientInstance()->isDir($this->testDir));
    }

    public function testGetFileContent()
    {
        $this->assertInternalType('string', $this->getFtpClientInstance()->getFileContent($this->testFile));
    }

    public function testLastMTime()
    {
        if (!$this->getFtpClientInstance()->isFeatureSupported('MDTM')) {
            $this->setExpectedException(FtpClientException::class);
            $this->getFtpClientInstance()->lastMTime($this->testFile);
        } else {
            $this->assertInternalType('int', $this->getFtpClientInstance()->lastMTime($this->testFile));
        }
    }

    public function testRemoveFile()
    {
        $this->assertTrue($this->getFtpClientInstance()->removeFile($this->testFile));
    }

    public function testRemoveDirectoryRecursive()
    {
        $this->getFtpClientInstance()->createDirectory($this->testDir);
        $this->getFtpClientInstance()->createFile("$this->testDir/$this->testFile");
        //$this->assertTrue($this->getFtpClientInstance()->removeDirectory($this->testDir));
    }

    public function testRemoveDirectory()
    {
        $this->assertTrue($this->getFtpClientInstance()->removeDirectory($this->testDir));
    }

    public function testAsyncUpload()
    {
        $localFile = tempnam(sys_get_temp_dir(), 'test.txt');
        file_put_contents($localFile, 'Hi there!');
        $this->assertTrue($this->getFtpClientInstance()->asyncUpload($localFile, $this->testFile, function () {
            // Do something
        }));
    }

    public function testUpload()
    {
        $localFile = tempnam(sys_get_temp_dir(), 'test.txt');
        file_put_contents($localFile, 'Hi there!');
        $this->assertTrue($this->getFtpClientInstance()->upload($localFile, $this->testFile));
        $this->getFtpClientInstance()->removeFile($this->testFile);
    }

    public function testRename()
    {
        $this->getFtpClientInstance()->createFile($this->testFile);
        $newName = $this->testFile . '_renamed';
        $this->assertTrue($this->getFtpClientInstance()->rename($this->testFile, $newName));
        $this->getFtpClientInstance()->removeFile($newName);
    }

    public function testKeepConnectionAlive()
    {
        $this->assertTrue($this->getFtpClientInstance()->keepConnectionAlive());
    }

    public function testGetFeatures()
    {
        $this->assertInternalType('array', $this->getFtpClientInstance()->getFeatures());
    }

    public function testBack()
    {
        $this->assertTrue($this->getFtpClientInstance()->back());
    }

    public function testGetCurrentDir()
    {
        $this->assertInternalType('string', $this->getFtpClientInstance()->getCurrentDir());
    }

    public function testIsFeatureSupported()
    {
        $this->assertFalse($this->getFtpClientInstance()->isFeatureSupported("someFeature"));
    }

    public function testAllocateSpace()
    {
        $this->assertTrue($this->getFtpClientInstance()->allocateSpace(256));
    }

    public function testGetSystem()
    {
        $this->assertInternalType('string', $this->getFtpClientInstance()->getSystem());
    }

    public function testGetCount()
    {
        $this->assertInternalType('int', $this->getFtpClientInstance()->getCount('.'));
    }

    public function testListDirectory()
    {
        $this->assertInternalType('array', $this->getFtpClientInstance()->listDirectory('.'));
    }

    public function testGetConnection()
    {
        $this->assertInstanceOf(ConnectionInterface::class, $this->getFtpClientInstance()->getConnection());
    }

    public function testSetCurrentDir()
    {
        $this->assertTrue($this->getFtpClientInstance()->setCurrentDir('.'));
    }

    public function testListDirectoryDetails()
    {
        $this->assertInternalType('array', $this->getFtpClientInstance()->listDirectoryDetails('.'));
    }

    protected function getFtpClientInstance()
    {
        return new FtpClient(ConnectionHelper::getConnection());
    }
}
