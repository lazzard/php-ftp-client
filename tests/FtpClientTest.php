<?php

namespace Lazzard\FtpClient\Tests;

use Lazzard\FtpClient\Connection\ConnectionInterface;
use Lazzard\FtpClient\Exception\FtpClientException;
use Lazzard\FtpClient\FtpClient;
use Lazzard\FtpClient\FtpWrapper;

class FtpClientTest extends \PHPUnit_Framework_TestCase
{
    protected static $tempFileName = 'FtpClientTestFile.txt';
    protected static $tempDirName  = 'FtpClientTestDirectory';

    public static function getFtpClient()
    {
        return new FtpClient(ConnectionHelper::getConnection());
    }

    public static function removeTempDir()
    {
        @ftp_rmdir(ConnectionHelper::getConnection()->getStream(), self::$tempDirName);
    }

    public static function createTempDir()
    {
        ftp_mkdir(ConnectionHelper::getConnection()->getStream(), self::$tempDirName);
    }

    public static function createTempFile($content = null)
    {
        // Create a file pointer to a temp file
        $handle = fopen('php://temp', 'a');
        fwrite($handle, (string)$content);
        rewind($handle); // Rewind position

        ftp_fput(ConnectionHelper::getConnection()->getStream(), self::$tempFileName, $handle, FtpWrapper::ASCII);
    }

    public static function removeTempFile()
    {
        @ftp_delete(ConnectionHelper::getConnection()->getStream(), self::$tempFileName);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(FtpClient::class, self::getFtpClient());
    }

    public function testGetParent()
    {
        $this->assertInternalType('string', self::getFtpClient()->getParent());
    }

    public function testFileSize()
    {
        self::createTempFile();
        $this->assertInternalType('int', self::getFtpClient()->fileSize(self::$tempFileName));
        self::removeTempFile();
    }

    public function testKeepConnectionAlive()
    {
        $this->assertTrue(self::getFtpClient()->keepConnectionAlive());
    }

    public function testGetFeatures()
    {
        $this->assertInternalType('array', self::getFtpClient()->getFeatures());
    }

    public function testBack()
    {
        $this->assertTrue(self::getFtpClient()->back());
    }

    public function testRemoveDirectory()
    {
        self::createTempDir();
        $this->assertTrue(self::getFtpClient()->removeDirectory(self::$tempDirName));
    }

    public function testRemoveDirectoryRecursive()
    {
        self::createTempDir();
        $tempFile = self::$tempDirName . '/hello.txt';
        self::getFtpClient()->createFile($tempFile);

        $this->assertTrue(self::getFtpClient()->removeDirectory(self::$tempDirName));
    }

    public function testGetTransferMode()
    {
        $this->assertEquals(2, self::getFtpClient()->getTransferMode('image.png'));
    }

    public function testMove()
    {
        self::createTempDir();
        self::createTempFile();

        $this->assertTrue(self::getFtpClient()->move(self::$tempFileName, self::$tempDirName));

        self::getFtpClient()->removeFile(self::$tempDirName . '/' . self::$tempFileName);
        self::removeTempDir();
    }

    public function testIsExists()
    {
        self::createTempFile();
        $this->assertTrue(self::getFtpClient()->isExists(self::$tempFileName));
    }


    public function testDownload()
    {
        self::createTempFile();

        $localFile = sys_get_temp_dir() . '/FtpClientTmpFile.txt';
        $this->assertTrue(self::getFtpClient()->download(self::$tempFileName, $localFile, false));

        self::removeTempFile();
        unlink($localFile);
    }

    public function testAsyncDownload()
    {
        self::createTempFile();

        $name = sys_get_temp_dir() . '/FtpClientTmpFile.txt';
        $this->assertTrue(self::getFtpClient()->asyncDownload(self::$tempFileName, $name, function () {
        }));

        self::removeTempFile();
        unlink($name);
    }

    public function testAsyncUpload()
    {
        $name = sys_get_temp_dir() . '/FtpClientTmpFile.txt';
        file_put_contents($name, 'Hi there!');

        $this->assertTrue(self::getFtpClient()->asyncUpload($name, self::$tempFileName, function () {
        }));

        self::removeTempFile();
    }

    public function testUpload()
    {
        $name = sys_get_temp_dir() . '/FtpClientTmpFile.txt';
        file_put_contents($name, 'Hi there!');

        $this->assertTrue(self::getFtpClient()->upload($name, self::$tempFileName));

        self::removeTempFile();
        unlink($name);
    }


    public function testDirSize()
    {
        self::createTempDir();
        $this->assertInternalType('int', self::getFtpClient()->dirSize(self::$tempDirName));
        self::removeTempFile();
    }

    public function testSetPermissionsFailure()
    {
        $mock = $this->getMockBuilder(FtpWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects(self::once())
            ->method('chmod')
            ->willReturn(false);

        $ftp = self::getFtpClient();
        $ftp->setWrapper($mock);

        $this->setExpectedException(FtpClientException::class);
        $ftp->setPermissions('no matter.txt', 744);
    }

    public function testSetPermissionsArrayParameter()
    {
        self::createTempFile();
        $this->assertTrue(self::getFtpClient()->setPermissions(self::$tempFileName, [
            'owner' => 'r-w',
            'group' => 'e',
            'other' => 'w-r'
        ]));
        self::removeTempFile();
    }

    public function testSetPermissionsNumericParameter()
    {
        self::createTempFile();
        $this->assertTrue(self::getFtpClient()->setPermissions(self::$tempFileName, 777));
        self::removeTempFile();
    }

    public function testIsEmptyDirectoryEmptyDirectory()
    {
        self::createTempDir();
        $this->assertTrue(self::getFtpClient()->isEmptyDirectory(self::$tempDirName));
        self::removeTempDir();
    }

    public function testIsEmptyDirectoryNonEmptyDirectory()
    {
        self::createTempDir();
        $tempFile = self::$tempDirName . '/hello.txt';
        self::getFtpClient()->createFile($tempFile);
        $this->assertFalse(self::getFtpClient()->isEmptyDirectory(self::$tempDirName));

        self::getFtpClient()->removeFile($tempFile);
        self::removeTempDir();
    }


    public function testRename()
    {
        self::createTempFile();
        $newName = self::$tempFileName . '_renamed';
        $this->assertTrue(self::getFtpClient()->rename(self::$tempFileName, $newName));
        self::getFtpClient()->removeFile($newName);
    }

    public function testIsFile()
    {
        self::createTempFile();
        $this->assertTrue(self::getFtpClient()->isFile(self::$tempFileName));
        self::removeTempFile();
    }

    public function testGetCurrentDir()
    {
        $this->assertInternalType('string', self::getFtpClient()->getCurrentDir());
    }

    public function testIsFeatureSupported()
    {
        $this->assertFalse(self::getFtpClient()->isFeatureSupported("SomeFeature"));
    }

    public function testAllocateSpace()
    {
        $this->assertTrue(self::getFtpClient()->allocateSpace(256));
    }

    public function testGetSystem()
    {
        $this->assertInternalType('string', self::getFtpClient()->getSystem());
    }

    public function testIsEmptyFile()
    {
        self::createTempFile();
        $this->assertTrue(self::getFtpClient()->isEmptyFile(self::$tempFileName));
        self::removeTempFile();
    }

    public function testIsDir()
    {
        $this->assertTrue(self::getFtpClient()->isDir('.'));
    }

    public function testGetDefaultTransferType()
    {
        $this->assertInternalType('string', self::getFtpClient()->getDefaultTransferType());
    }

    public function testGetFileContent()
    {
        self::createTempFile();
        $this->assertInternalType('string', self::getFtpClient()->getFileContent(self::$tempFileName));
        self::removeTempFile();
    }

    public function testGetCount()
    {
        $this->assertInternalType('int', self::getFtpClient()->getCount('.'));
    }

    public function testCreateFileWithoutContent()
    {
        $this->assertTrue(self::getFtpClient()->createFile(self::$tempFileName));
        self::removeTempFile();
    }

    public function testCreateFileWithContent()
    {
        $this->assertTrue(
            self::getFtpClient()->createFile(self::$tempFileName),
            "This is a temporary file, it will be removed after a little while."
        );
        self::removeTempFile();
    }

    public function testListDirectory()
    {
        $this->assertInternalType('array', self::getFtpClient()->listDirectory('.'));
    }

    public function testRemoveFile()
    {
        self::createTempFile();
        $this->assertTrue(self::getFtpClient()->removeFile(self::$tempFileName));
    }

    public function testGetConnection()
    {
        $this->assertInstanceOf(ConnectionInterface::class, self::getFtpClient()->getConnection());
    }

    public function testCreateDirectory()
    {
        $this->assertTrue(
            self::getFtpClient()->createDirectory(self::$tempDirName),
            "This is a temporary directory, it will be removed after a little while."
        );
        self::removeTempDir();
    }

    public function testSetCurrentDirSuccess()
    {
        $this->assertTrue(self::getFtpClient()->setCurrentDir('.'));
    }

    public function testSetCurrentDirFailure()
    {
        $mock = $this->getMockBuilder(FtpWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('chdir')
            ->willReturn(false);

        $ftp = self::getFtpClient();
        $ftp->setWrapper($mock);

        $this->setExpectedException(FtpClientException::class);

        $ftp->setCurrentDir('.');
    }

    public function testLastMTime()
    {
        self::createTempFile();
        if (!self::getFtpClient()->isFeatureSupported('MDTM')) {
            $this->setExpectedException(FtpClientException::class);
            self::getFtpClient()->lastMTime(self::$tempFileName);
        } else {
            $this->assertInternalType('int', self::getFtpClient()->lastMTime(self::$tempFileName));
        }
        self::removeTempFile();
    }

    public function testListDirectoryDetails()
    {
        $this->assertInternalType('array', self::getFtpClient()->listDirectoryDetails('.'));
    }

    protected function tearDown()
    {
        self::removeTempFile();
        self::removeTempDir();
    }
}
