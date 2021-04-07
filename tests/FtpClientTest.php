<?php

namespace Lazzard\FtpClient\Tests;

use Lazzard\FtpClient\Connection\ConnectionInterface;
use Lazzard\FtpClient\Exception\FtpClientException;
use Lazzard\FtpClient\FtpClient;
use Lazzard\FtpClient\FtpWrapper;
use PHPUnit\Framework\TestCase;

class FtpClientTest extends TestCase
{
    protected $testFile = INITIAL_DIR . '/lazzard_ftp_client_test_file.txt';
    protected $testDir  = INITIAL_DIR . '/lazzard_ftp_client_test_directory';

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
        $this->getFtpClientInstance()->removeFile($this->testFile);
    }

    public function testCreateFileWithContent()
    {
        if ($this->getFtpClientInstance()->createFile($this->testFile, "content ...!")) {
            $this->assertTrue(true);
            $this->getFtpClientInstance()->removeFile($this->testFile);
        }
    }

    public function testcreateDir()
    {
        if ($this->getFtpClientInstance()->createDir($this->testDir)) {
            $this->assertTrue(true);
            $this->getFtpClientInstance()->removeDir($this->testDir);
        }
    }

    public function testFileSize()
    {
        if ($this->getFtpClientInstance()->createFile($this->testFile)) {
            $this->assertInternalType('int', $this->getFtpClientInstance()->fileSize($this->testFile));
            $this->getFtpClientInstance()->removeFile($this->testFile);
        }
    }

    public function testDirSize()
    {
        if ($this->getFtpClientInstance()->createDir($this->testDir)) {
            $this->assertInternalType('int', $this->getFtpClientInstance()->dirSize($this->testDir));
            $this->getFtpClientInstance()->removeDir($this->testDir);
        }
    }

    public function testDownload()
    {
        if ($this->getFtpClientInstance()->createFile($this->testFile)) {
            $localFile = tempnam(sys_get_temp_dir(), 'test.txt');
            $this->assertTrue($this->getFtpClientInstance()->download($this->testFile, $localFile));
            $this->getFtpClientInstance()->removeFile($this->testFile);
        }
    }

    public function testAsyncDownload()
    {
        if ($this->getFtpClientInstance()->createFile($this->testFile)) {
            $localFile = tempnam(sys_get_temp_dir(), 'test2.txt');
            $this->assertTrue($this->getFtpClientInstance()->asyncDownload($this->testFile, $localFile, function() {}));
            $this->getFtpClientInstance()->removeFile($this->testFile);
        }
    }

    public function testMove()
    {
        $testFile = INITIAL_DIR . '/test_move.txt';
        if ($this->getFtpClientInstance()->createFile($testFile)
            && $this->getFtpClientInstance()->createDir($this->testDir)) {
                $this->assertTrue($this->getFtpClientInstance()->move($testFile, $this->testDir));
                $this->getFtpClientInstance()->removeDir($this->testDir);
        }
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
        if ($this->getFtpClientInstance()->createFile($this->testFile)) {
            $this->assertTrue($this->getFtpClientInstance()->setPermissions($this->testFile, [
                'owner' => 'r-w',
                'group' => 'e',
                'other' => 'w-r'
            ]));
            $this->getFtpClientInstance()->removeFile($this->testFile);
        }
    }

    public function testSetPermissionsWithNumericParameter()
    {
        if ($this->getFtpClientInstance()->createFile($this->testFile)) {
            $this->assertTrue($this->getFtpClientInstance()->setPermissions($this->testFile, 777));
            $this->getFtpClientInstance()->removeFile($this->testFile);
        }
    }

    public function testIsEmptyWithAnEmptyFile()
    {
        if ($this->getFtpClientInstance()->createFile($this->testFile)) {
            $this->assertTrue($this->getFtpClientInstance()->isEmpty($this->testFile));
            $this->getFtpClientInstance()->removeFile($this->testFile);
        }   
    }

    public function testIsEmptyWithANonEmptyFile()
    {
        if ($this->getFtpClientInstance()->createFile($this->testFile, "content...!")) {
            $this->assertFalse($this->getFtpClientInstance()->isEmpty($this->testFile));
            $this->getFtpClientInstance()->removeFile($this->testFile);
        }   
    }

    public function testIsEmptyWithAnEmptyDirectory()
    {
        if ($this->getFtpClientInstance()->createDir($this->testDir)) {
            $this->assertTrue($this->getFtpClientInstance()->isEmpty($this->testDir));
            $this->getFtpClientInstance()->removeDir($this->testDir);
        }
    }

    public function testIsEmptyWithANonEmptyDirectory()
    {
        if ($this->getFtpClientInstance()->createDir($this->testDir)
            && $this->getFtpClientInstance()->createFile($this->testDir . '/test.txt')) {
                $this->assertFalse($this->getFtpClientInstance()->isEmpty($this->testDir));
                $this->getFtpClientInstance()->removeDir($this->testDir);
        }
    }

    public function testIsFile()
    {
        if ($this->getFtpClientInstance()->createFile($this->testFile)) {
            $this->assertTrue($this->getFtpClientInstance()->isFile($this->testFile));
            $this->getFtpClientInstance()->removeFile($this->testFile);
        }
    }

    public function testIsDir()
    {
        if ($this->getFtpClientInstance()->createDir($this->testDir)) {
            $this->assertTrue($this->getFtpClientInstance()->isDir($this->testDir));
            $this->getFtpClientInstance()->removeDir($this->testDir);
        }
    }

    public function testGetFileContent()
    {
        if ($this->getFtpClientInstance()->createFile($this->testFile)) {
            $this->assertInternalType('string', $this->getFtpClientInstance()->getFileContent($this->testFile));
            $this->getFtpClientInstance()->removeFile($this->testFile);
        }
    }

    public function testLastMTime()
    {
        if (!$this->getFtpClientInstance()->isFeatureSupported('MDTM')) {
            $this->setExpectedException(FtpClientException::class);
            $this->getFtpClientInstance()->lastMTime($this->testFile);
        } elseif ($this->getFtpClientInstance()->createFile($this->testFile)) {
            $this->assertInternalType('int', $this->getFtpClientInstance()->lastMTime($this->testFile));
            $this->getFtpClientInstance()->removeFile($this->testFile);
        }
    }

    public function testRemoveFile()
    {
        if ($this->getFtpClientInstance()->createFile($this->testFile)) {
            $this->assertTrue($this->getFtpClientInstance()->removeFile($this->testFile));
        }
    }

    public function testRemoveDirectoryRecursive()
    {
        if ($this->getFtpClientInstance()->createDir($this->testDir)
            && $this->getFtpClientInstance()->createFile($this->testDir . '/test.txt')) {
                $this->assertTrue($this->getFtpClientInstance()->removeDir($this->testDir));
        }
    }

    public function testRemoveDirectory()
    {
        if ($this->getFtpClientInstance()->createDir($this->testDir)) {
            $this->assertTrue($this->getFtpClientInstance()->removeDir($this->testDir));
        }
    }

    public function testAsyncUpload()
    {
        $localFile = tempnam(sys_get_temp_dir(), 'test.txt');
        file_put_contents($localFile, 'Hi there!');
        $this->assertTrue($this->getFtpClientInstance()->asyncUpload($localFile, $this->testFile, function() {}));
        $this->getFtpClientInstance()->removeFile($this->testFile);
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
        if ($this->getFtpClientInstance()->createFile($this->testFile)) {
            $newName = $this->testFile . '_renamed';
            $this->assertTrue($this->getFtpClientInstance()->rename($this->testFile, $newName));
            $this->getFtpClientInstance()->removeFile($newName);
        }
    }

    public function testKeepConnectionAlive()
    {
        $this->assertTrue($this->getFtpClientInstance()->keepAlive());
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
        $this->assertInternalType('array', $this->getFtpClientInstance()->listDir('.'));
    }

    public function testGetConnection()
    {
        $this->assertInstanceOf(ConnectionInterface::class, $this->getFtpClientInstance()->getConnection());
    }

    public function testchangeDir()
    {
        $this->assertTrue($this->getFtpClientInstance()->changeDir('.'));
    }

    public function testListDirectoryDetails()
    {
        $this->assertInternalType('array', $this->getFtpClientInstance()->listDirDetails('.'));
    }

    public function testCopyFromLocalWithDirectorySource()
    {
        $testDir    = basename($this->testDir);
        $testFile   = basename($this->testFile);
        $tempDirPah = sys_get_temp_dir() . "/$testDir";
        $tempFile   = "$tempDirPah/$testFile";

        if (!file_exists($tempDirPah)) {
            if (!mkdir($tempDirPah, 0777) || file_put_contents($tempFile, 'hello world!!') === false) {
                self::markTestSkipped();
            }
        }

        $this->assertTrue($this->getFtpClientInstance()->copyFromLocal($tempDirPah, INITIAL_DIR));
        $this->getFtpClientInstance()->removeDir(INITIAL_DIR . "/$testDir");
    }

    public function testCopyFromLocalWithFileSource()
    {
        $testFile = basename($this->testFile);
        $tempFile = sys_get_temp_dir() . "/$testFile";

        if (file_put_contents($tempFile, 'hello world!!') === false) {
            self::markTestSkipped();
        }

        $this->assertTrue($this->getFtpClientInstance()->copyFromLocal($tempFile, INITIAL_DIR));
        $this->getFtpClientInstance()->removeFile(INITIAL_DIR . "/$testFile");
    }

    protected function getFtpClientInstance()
    {
        return new FtpClient(ConnectionHelper::getConnection());
    }
}
