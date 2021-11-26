<?php

namespace Lazzard\FtpClient\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Lazzard\FtpClient\FtpClient;

class FtpClientTest extends TestCase
{
    protected static $testFile;
    protected static $testDir;

    public static function setUpBeforeClass() : void
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

    public function testConstructor() : void
    {
        $this->assertInstanceOf(FtpClient::class, new FtpClient(ConnectionHelper::getConnection()));
    }

    public function testGetParent() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertIsString($client->getParent());
    }

    public function testCreateFileWithoutContent() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertTrue($client->createFile(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testCreateFileWithContent() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertTrue($client->createFile(self::$testFile, 'some content'));

        $client->removeFile(self::$testFile);
    }

    public function testCreateDir() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertTrue($client->createDir(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testCreateDirRecursiveCreation() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertTrue($client->createDir(self::$testDir . '/' . basename(self::$testDir)));

        $client->removeDir(self::$testDir);
    }

    public function testFileSize() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile, 'some content');

        $this->assertIsInt($client->fileSize(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testDirSizeWithEmptyDirectory() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);

        $this->assertIsInt($client->dirSize(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testDirSizeWithNonEmptyDirectory() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testDir . "/" . basename(self::$testFile), 'content');

        $this->assertIsInt($client->dirSize(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testDownload() : void
    {
        $localFile = tempnam(sys_get_temp_dir(), 'testDownload');

        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile, 'some content');

        $this->assertTrue($client->download(self::$testFile, $localFile));
        $this->assertFileExists($localFile);

        $client->removeFile(self::$testFile);

        unlink($localFile);
    }

    public function testAsyncDownload() : void
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

    public function testMove() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $client->createDir(self::$testDir);

        $this->assertTrue($client->move(self::$testFile, self::$testDir));
        $this->assertTrue($client->isExists(self::$testDir . "/" . basename(self::$testFile)));

        $client->removeDir(self::$testDir);
    }

    public function testSetPermissionsWithArrayParameter() : void
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

    public function testSetPermissionsWithNumericParameter() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $this->assertTrue($client->setPermissions(self::$testFile, 777));

        $client->removeFile(self::$testFile);
    }

    public function testIsEmptyWithEmptyFile() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $this->assertTrue($client->isEmpty(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testIsEmptyWithNonEmptyFile() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile, "some content");

        $this->assertFalse($client->isEmpty(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testIsEmptyWithEmptyDirectory() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);

        $this->assertTrue($client->isEmpty(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testIsEmptyWithNonEmptyDirectory() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testDir . "/" . basename(self::$testFile), 'content');

        $this->assertFalse($client->isEmpty(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testIsFileWithFile() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $this->assertTrue($client->isFile(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testIsFileWithDirectory() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);

        $this->assertFalse($client->isFile(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testIsDirWithDirectory() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);

        $this->assertTrue($client->isDir(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testIsDirWithFile() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $this->assertFalse($client->isDir(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testGetFileContent() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile, "some content");

        $this->assertIsString($client->getFileContent(self::$testFile));
        $this->assertSame("some content", $client->getFileContent(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testLastMTimeWithoutFormat() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $this->assertIsInt($client->lastMTime(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testLastMTimeWithFormat() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $this->assertIsString($client->lastMTime(self::$testFile, 'Y-m-d'));

        $client->removeFile(self::$testFile);
    }

    public function testRemoveFile() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $this->assertTrue($client->removeFile(self::$testFile));
    }

    public function testRemoveDirectory() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);

        $this->assertTrue($client->removeDir(self::$testDir));
    }

    public function testUpload() : void
    {
        $localFile = tempnam(sys_get_temp_dir(), 'testUpload');

        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertTrue($client->upload($localFile, self::$testFile));
        $this->assertTrue($client->isExists(self::$testFile));

        $client->removeFile(self::$testFile);

        unlink($localFile);
    }

    public function testAsyncUpload() : void
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

    public function testRenameWithFile() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $renamed = self::$testFile . '_renamed';

        $this->assertTrue($client->rename(self::$testFile, $renamed));

        $client->removeFile($renamed);
    }

    public function testRenameWithDirectory() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);

        $renamed = self::$testDir . '_renamed';

        $this->assertTrue($client->rename(self::$testDir, $renamed));

        $client->removeDir($renamed);
    }

    public function testKeepConnectionAlive() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertTrue($client->keepAlive());
    }

    public function testGetFeatures() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertIsArray($client->getFeatures());
    }

    public function testBack() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $original = $client->getCurrentDir();

        $this->assertTrue($client->back());

        $client->changeDir($original);
    }

    public function testGetCurrentDir() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);

        $this->assertIsString($client->getCurrentDir());
    }

    public function testIsFeatureSupported() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertFalse($client->isFeatureSupported("my feature"));
    }

    public function testAllocateSpace() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertTrue($client->allocateSpace(256));
    }

    public function testGetSystem() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertIsString($client->getSystem());
    }

    public function testGetCount() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testDir . "/" . basename(self::$testFile));

        $this->assertSame(1, $client->getCount(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testGetCountRecursive() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testDir . "/" . basename(self::$testFile));

        $this->assertSame(1, $client->getCount(self::$testDir, true));

        $client->removeDir(self::$testDir);
    }

    public function testListDir() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testDir . '/' . basename(self::$testFile));

        $this->assertSame([basename(self::$testFile)], $client->listDir(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testChangeDir() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertTrue($client->changeDir('.'));
    }

    public function testListDirectoryDetails() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testDir . '/' . basename(self::$testFile));

        $this->assertCount(1, $client->listDirDetails(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testCopyFromLocalWithDirectory() : void
    {
        $localDir = sys_get_temp_dir() . "testCopyFromLocalWithDirectory";

        @mkdir($localDir);

        tempnam($localDir, 'testCopyFromLocalWithDirectory');

        $client = new FtpClient(ConnectionHelper::getConnection());

        $this->assertTrue($client->copyFromLocal($localDir, self::$testDir));
        $this->assertTrue($client->isDir(self::$testDir . "/" . basename($localDir)));

        $client->removeDir(self::$testDir);

        @unlink($localDir);
    }

    public function testCopyFromLocalWithFile() : void
    {
        $localFile = tempnam(sys_get_temp_dir(), 'testCopyFromLocalWithFile');

        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);

        $this->assertTrue($client->copyFromLocal($localFile, self::$testDir));
        $this->assertTrue($client->isFile(self::$testDir . "/" . basename($localFile)));

        $client->removeDir(self::$testDir);

        unlink($localFile);
    }

    public function testCopyToLocalWithFile() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $this->assertTrue($client->copyToLocal(self::$testFile, sys_get_temp_dir()));
        $this->assertFileExists(sys_get_temp_dir() . "/" . basename(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testCopyToLocalWithDirectory() : void
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

    public function testFind() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createFile(self::$testFile);

        $this->assertNotEmpty($client->find('/.*\.txt$/i', dirname(self::$testFile)));

        $client->removeFile(self::$testFile);
    }

    public function testFindRecursive() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $deepDir = self::$testDir . '/' . basename(self::$testDir);

        $client->createDir($deepDir);
        $client->createFile($deepDir . '/' . basename(self::$testFile));

        $this->assertNotEmpty($client->find('/.*\.txt$/i', self::$testDir, true));

        $client->removeDir(self::$testDir);
    }

    public function testCopyWithFile() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testFile);

        $this->assertTrue($client->copy(self::$testFile, self::$testDir));
        $this->assertTrue($client->isFile(self::$testDir . "/" . basename(self::$testFile)));

        $client->removeFile(self::$testFile);
        $client->removeDir(self::$testDir);
    }

    public function testCopyWithDirectory() : void
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

    public function testAppendFile() : void
    {
        $client = new FtpClient(ConnectionHelper::getConnection());

        $testFile = self::$testFile;

        $client->createFile($testFile);

        $this->assertTrue($client->appendFile($testFile, 'hello world!'));

        $client->removeFile($testFile);
    }
}