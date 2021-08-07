<?php

namespace Lazzard\FtpClient\Tests\Integration;

use Lazzard\FtpClient\FtpClient;
use PHPUnit\Framework\TestCase;

class FtpClientTest extends TestCase
{
    protected static $testFile;
    protected static $testDir;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $initialDir = trim(INITIAL_DIR, '/');

        if (!self::$testFile) {
            self::$testFile = $initialDir . '/lazzard_ftp_client_test_file.txt';
        }

        if (!self::$testDir) {
            self::$testDir = $initialDir . '/lazzard_ftp_client_test_directory';
        }
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(FtpClient::class, new FtpClient(ConnectionHelper::getConnection()));
    }

    public function testGetParent()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertInternalType('string', $client->getParent());
    }

    public function testCreateFileWithoutContent()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertTrue($client->createFile(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testCreateFileWithContent()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertTrue($client->createFile(self::$testFile, 'some content'));

        $client->removeFile(self::$testFile);
    }

    public function testCreateDir()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertTrue($client->createDir(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testCreateDirRecursiveCreation()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertTrue($client->createDir(self::$testDir . '/' . basename(self::$testDir)));

        $client->removeDir(self::$testDir);
    }

    public function testFileSize()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile, 'some content');

        $this->assertInternalType('int', $client->fileSize(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testDirSizeWithEmptyDirectory()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);

        $this->assertInternalType('int', $client->dirSize(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testDirSizeWithNonEmptyDirectory()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testDir . "/" . basename(self::$testFile), 'content');

        $this->assertInternalType('int', $client->dirSize(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testDownload()
    {
        $localFile = tempnam(sys_get_temp_dir(), 'testDownload');

        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile, 'some content');

        $this->assertTrue($client->download(self::$testFile, $localFile));
        $this->assertFileExists($localFile);

        $client->removeFile(self::$testFile);

        unlink($localFile);
    }

    public function testAsyncDownload()
    {
        $localFile = tempnam(sys_get_temp_dir(), 'testAsyncDownload');

        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile, 'some content');

        $this->assertTrue($client->asyncDownload(self::$testFile, $localFile, function () {
            //
        }));
        $this->assertFileExists($localFile);

        $client->removeFile(self::$testFile);

        unlink($localFile);
    }


    public function testMove()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $client->createDir(self::$testDir);

        $this->assertTrue($client->move(self::$testFile, self::$testDir));
        $this->assertTrue($client->isExists(self::$testDir . "/" . basename(self::$testFile)));

        $client->removeDir(self::$testDir);
    }


    public function testSetPermissionsWithArrayParameter()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $this->assertTrue($client->setPermissions(self::$testFile, [
            'owner' => 'r-w',
            'group' => 'e',
            'other' => 'w-r'
        ]));

        $client->removeFile(self::$testFile);
    }


    public function testSetPermissionsWithNumericParameter()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $this->assertTrue($client->setPermissions(self::$testFile, 777));

        $client->removeFile(self::$testFile);
    }

    public function testIsEmptyWithEmptyFile()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $this->assertTrue($client->isEmpty(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testIsEmptyWithNonEmptyFile()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile, "some content");

        $this->assertFalse($client->isEmpty(self::$testFile));

        $client->removeFile(self::$testFile);
    }


    public function testIsEmptyWithEmptyDirectory()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);

        $this->assertTrue($client->isEmpty(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testIsEmptyWithNonEmptyDirectory()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testDir . "/" . basename(self::$testFile), 'content');

        $this->assertFalse($client->isEmpty(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testIsFileWithFile()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $this->assertTrue($client->isFile(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testIsFileWithDirectory()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);

        $this->assertFalse($client->isFile(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testIsDirWithDirectory()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);

        $this->assertTrue($client->isDir(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testIsDirWithFile()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $this->assertFalse($client->isDir(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testGetFileContent()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile, "some content");

        $this->assertInternalType('string', $client->getFileContent(self::$testFile));
        $this->assertSame("some content", $client->getFileContent(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testLastMTimeWithoutFormat()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $this->assertInternalType('int', $client->lastMTime(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testLastMTimeWithFormat()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $this->assertInternalType('string', $client->lastMTime(self::$testFile, 'Y-m-d'));

        $client->removeFile(self::$testFile);
    }

    public function testRemoveFile()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $this->assertTrue($client->removeFile(self::$testFile));
    }

    public function testRemoveDirectory()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);

        $this->assertTrue($client->removeDir(self::$testDir));
    }

    public function testUpload()
    {
        $localFile = tempnam(sys_get_temp_dir(), 'testUpload');

        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertTrue($client->upload($localFile, self::$testFile));
        $this->assertTrue($client->isExists(self::$testFile));

        $client->removeFile(self::$testFile);

        unlink($localFile);
    }

    public function testAsyncUpload()
    {
        $localFile = tempnam(sys_get_temp_dir(), 'testUpload');

        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertTrue($client->asyncUpload($localFile, self::$testFile, function () {
            //
        }));
        $this->assertTrue($client->isExists(self::$testFile));

        $client->removeFile(self::$testFile);

        unlink($localFile);
    }

    public function testRenameWithFile()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $renamed = self::$testFile . '_renamed';

        $this->assertTrue($client->rename(self::$testFile, $renamed));

        $client->removeFile($renamed);
    }

    public function testRenameWithDirectory()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);

        $renamed = self::$testDir . '_renamed';

        $this->assertTrue($client->rename(self::$testDir, $renamed));

        $client->removeDir($renamed);
    }

    public function testKeepConnectionAlive()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertTrue($client->keepAlive());
    }

    public function testGetFeatures()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertInternalType('array', $client->getFeatures());
    }

    public function testBack()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertTrue($client->back());
    }

    public function testGetCurrentDir()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);

        $this->assertInternalType('string', $client->getCurrentDir());
    }

    public function testIsFeatureSupported()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertFalse($client->isFeatureSupported("my feature"));
    }

    public function testAllocateSpace()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertTrue($client->allocateSpace(256));
    }

    public function testGetSystem()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertInternalType('string', $client->getSystem());
    }

    public function testGetCount()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testDir . "/" . basename(self::$testFile));

        $this->assertSame(1, $client->getCount(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testGetCountRecursive()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testDir . "/" . basename(self::$testFile));

        $this->assertSame(1, $client->getCount(self::$testDir, true));

        $client->removeDir(self::$testDir);
    }

    public function testListDir()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testDir . '/' . basename(self::$testFile));

        $this->assertSame([basename(self::$testFile)], $client->listDir(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testChangeDir()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertTrue($client->changeDir('.'));
    }

    public function testListDirectoryDetails()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testDir . '/' . basename(self::$testFile));

        $this->assertCount(1, $client->listDirDetails(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testCopyFromLocalWithDirectory()
    {
        $localDir = sys_get_temp_dir() . "testCopyFromLocalWithDirectory";

        @mkdir($localDir);

        tempnam($localDir, 'testCopyFromLocalWithDirectory');

        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);

        $this->assertTrue($client->copyFromLocal($localDir, self::$testDir));
        $this->assertTrue($client->isDir(self::$testDir . "/" . basename($localDir)));

        $client->removeDir(self::$testDir);

        @unlink($localDir);
    }

    public function testCopyFromLocalWithFile()
    {
        $localFile = tempnam(sys_get_temp_dir(), 'testCopyFromLocalWithFile');

        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);

        $this->assertTrue($client->copyFromLocal($localFile, self::$testDir));
        $this->assertTrue($client->isFile(self::$testDir . "/" . basename($localFile)));

        $client->removeDir(self::$testDir);

        unlink($localFile);
    }

    public function testCopyToLocalWithFile()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $this->assertTrue($client->copyToLocal(self::$testFile, sys_get_temp_dir()));
        $this->assertFileExists(sys_get_temp_dir() . "/" . basename(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testCopyToLocalWithDirectory()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testDir . "/" . basename(self::$testFile), 'content');

        $this->assertTrue($client->copyToLocal(self::$testDir, sys_get_temp_dir()));

        $copiedFile = sys_get_temp_dir() . "/" . basename(self::$testDir);

        $this->assertTrue(file_exists($copiedFile));

        @unlink($copiedFile);

        $client->removeDir(self::$testDir);
    }

    public function testFind()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $this->assertNotEmpty($client->find('/.*\.txt$/i', dirname(self::$testFile)));

        $client->removeFile(self::$testFile);
    }

    public function testFindRecursive()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $deepDir = self::$testDir . '/' . basename(self::$testDir);

        $client->createDir($deepDir);
        $client->createFile($deepDir . '/' . basename(self::$testFile));

        $this->assertNotEmpty($client->find('/.*\.txt$/i', self::$testDir, true));

        $client->removeDir(self::$testDir);
    }

    public function testCopyWithFile()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testFile);

        $this->assertTrue($client->copy(self::$testFile, self::$testDir));
        $this->assertTrue($client->isFile(self::$testDir . "/" . basename(self::$testFile)));

        $client->removeFile(self::$testFile);
        $client->removeDir(self::$testDir);
    }

    public function testCopyWithDirectory()
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $testDir2 = self::$testDir . "_2";

        $client->createDir($testDir2);
        $client->createDir(self::$testDir);
        $client->createFile(self::$testDir . '/' . basename(self::$testFile));

        $this->assertTrue($client->copy(self::$testDir, $testDir2));
        $this->assertTrue($client->isDir($testDir2 . '/' . basename(self::$testDir)));

        $client->removeDir($testDir2);
        $client->removeDir(self::$testDir);
    }
}