<?php

namespace Lazzard\FtpClient\Tests\Integration;

use Lazzard\FtpClient\FtpClient;
use PHPUnit\Framework\TestCase;

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

    public function testGetParent() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $this->assertIsString($client->getParent());
    }

    public function testCreateFileWithoutContent() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $this->assertTrue($client->createFile(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testCreateFileWithContent() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $this->assertTrue($client->createFile(self::$testFile, 'some content'));

        $client->removeFile(self::$testFile);
    }

    public function testCreateDir() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $this->assertTrue($client->createDir(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testCreateDirRecursiveCreation() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $this->assertTrue($client->createDir(self::$testDir . '/' . basename(self::$testDir)));

        $client->removeDir(self::$testDir);
    }

    public function testFileSize() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createFile(self::$testFile, 'some content');

        $this->assertIsInt($client->fileSize(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testDirSizeWithEmptyDirectory() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createDir(self::$testDir);

        $this->assertIsInt($client->dirSize(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testDirSizeWithNonEmptyDirectory() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testDir . "/" . basename(self::$testFile), 'content');

        $this->assertIsInt($client->dirSize(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testDownload() : void
    {
        $localFile = tempnam(sys_get_temp_dir(), 'testDownload');

        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createFile(self::$testFile, 'some content');

        $this->assertTrue($client->download(self::$testFile, $localFile));
        $this->assertFileExists($localFile);

        $client->removeFile(self::$testFile);

        unlink($localFile);
    }

    public function testAsyncDownload() : void
    {
        $localFile = tempnam(sys_get_temp_dir(), 'testAsyncDownload');

        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createFile(self::$testFile, 'some content');

        $this->assertTrue($client->asyncDownload(self::$testFile, $localFile, function ($speed, $percentage, $transferred, $seconds) {
            //
        }));
        $this->assertFileExists($localFile);

        $client->removeFile(self::$testFile);

        unlink($localFile);
    }

    public function testMove() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createFile(self::$testFile);

        $client->createDir(self::$testDir);

        $this->assertTrue($client->move(self::$testFile, self::$testDir));
        $this->assertTrue($client->isExists(self::$testDir . "/" . basename(self::$testFile)));

        $client->removeDir(self::$testDir);
    }

    public function testSetPermissionsWithArrayParameter() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

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
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createFile(self::$testFile);

        $this->assertTrue($client->setPermissions(self::$testFile, 777));

        $client->removeFile(self::$testFile);
    }

    public function testIsEmptyWithEmptyFile() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createFile(self::$testFile);

        $this->assertTrue($client->isEmpty(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testIsEmptyWithNonEmptyFile() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createFile(self::$testFile, "some content");

        $this->assertFalse($client->isEmpty(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testIsEmptyWithEmptyDirectory() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createDir(self::$testDir);

        $this->assertTrue($client->isEmpty(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testIsEmptyWithNonEmptyDirectory() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testDir . "/" . basename(self::$testFile), 'content');

        $this->assertFalse($client->isEmpty(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testIsFileWithFile() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createFile(self::$testFile);

        $this->assertTrue($client->isFile(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testIsFileWithDirectory() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createDir(self::$testDir);

        $this->assertFalse($client->isFile(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testIsDirWithDirectory() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createDir(self::$testDir);

        $this->assertTrue($client->isDir(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testIsDirWithFile() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createFile(self::$testFile);

        $this->assertFalse($client->isDir(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testGetFileContent() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createFile(self::$testFile, "some content");

        $this->assertIsString($client->getFileContent(self::$testFile));
        $this->assertSame("some content", $client->getFileContent(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testLastMTimeWithoutFormat() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createFile(self::$testFile);

        $this->assertIsInt($client->lastMTime(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testLastMTimeWithFormat() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createFile(self::$testFile);

        $this->assertIsString($client->lastMTime(self::$testFile, 'Y-m-d'));

        $client->removeFile(self::$testFile);
    }

    public function testRemoveFile() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createFile(self::$testFile);

        $this->assertTrue($client->removeFile(self::$testFile));
    }

    public function testRemoveDirectory() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createDir(self::$testDir);

        $this->assertTrue($client->removeDir(self::$testDir));
    }

    public function testUpload() : void
    {
        $localFile = tempnam(sys_get_temp_dir(), 'testUpload');

        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $this->assertTrue($client->upload($localFile, self::$testFile));
        $this->assertTrue($client->isExists(self::$testFile));

        $client->removeFile(self::$testFile);

        unlink($localFile);
    }

    public function testAsyncUpload() : void
    {
        $localFile = tempnam(sys_get_temp_dir(), 'testUpload');

        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $this->assertTrue($client->asyncUpload($localFile, self::$testFile, function () {
            //
        }));
        $this->assertTrue($client->isExists(self::$testFile));

        $client->removeFile(self::$testFile);

        unlink($localFile);
    }

    public function testRenameWithFile() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createFile(self::$testFile);

        $renamed = self::$testFile . '_renamed';

        $this->assertTrue($client->rename(self::$testFile, $renamed));

        $client->removeFile($renamed);
    }

    public function testRenameWithDirectory() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createDir(self::$testDir);

        $renamed = self::$testDir . '_renamed';

        $this->assertTrue($client->rename(self::$testDir, $renamed));

        $client->removeDir($renamed);
    }

    public function testKeepConnectionAlive() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $this->assertTrue($client->keepAlive());
    }

    public function testGetFeatures() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $this->assertIsArray($client->getFeatures());
    }

    public function testBack() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $original = $client->getCurrentDir();

        $this->assertTrue($client->back());

        $client->changeDir($original);
    }

    public function testGetCurrentDir() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createDir(self::$testDir);

        $this->assertIsString($client->getCurrentDir());
    }

    public function testIsFeatureSupported() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $this->assertFalse($client->isFeatureSupported("my feature"));
    }

    public function testAllocateSpace() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $this->assertTrue($client->allocateSpace(256));
    }

    public function testGetSystem() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $this->assertIsString($client->getSystem());
    }

    public function testGetCount() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testDir . "/" . basename(self::$testFile));

        $this->assertSame(1, $client->getCount(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testGetCountRecursive() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testDir . "/" . basename(self::$testFile));

        $this->assertSame(1, $client->getCount(self::$testDir, true));

        $client->removeDir(self::$testDir);
    }

    public function testListDir() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testDir . '/' . basename(self::$testFile));

        $this->assertSame([basename(self::$testFile)], $client->listDir(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testChangeDir() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $this->assertTrue($client->changeDir('.'));
    }

    public function testListDirectoryDetails() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testDir . '/' . basename(self::$testFile));

        $this->assertCount(1, $client->listDirDetails(self::$testDir));

        $client->removeDir(self::$testDir);
    }

    public function testCopyFromLocalWithDirectory() : void
    {
        $localDir = sys_get_temp_dir() . "/testCopyFromLocalWithDirectory";

        @mkdir($localDir);

        tempnam($localDir, 'testCopyFromLocalWithDirectory');

        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $this->assertTrue($client->copyFromLocal($localDir, self::$testDir));
        $this->assertTrue($client->isDir(self::$testDir . "/" . basename($localDir)));

        $client->removeDir(self::$testDir);

        @unlink($localDir);
    }

    public function testCopyFromLocalWithFile() : void
    {
        $localFile = tempnam(sys_get_temp_dir(), 'testCopyFromLocalWithFile');

        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createDir(self::$testDir);

        $this->assertTrue($client->copyFromLocal($localFile, self::$testDir));
        $this->assertTrue($client->isFile(self::$testDir . "/" . basename($localFile)));

        $client->removeDir(self::$testDir);

        unlink($localFile);
    }

    public function testCopyToLocalWithFile() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createFile(self::$testFile);

        @mkdir('./tmp', 0777, true);

        $this->assertTrue($client->copyToLocal(self::$testFile, sys_get_temp_dir()));
        $this->assertFileExists("./tmp/" . basename(self::$testFile));

        $client->removeFile(self::$testFile);
    }

    public function testCopyToLocalWithDirectory() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testDir . "/" . basename(self::$testFile), 'content');

        $this->assertTrue($client->copyToLocal(self::$testDir, sys_get_temp_dir()));

        $copiedFile = "./tmp/" . basename(self::$testDir);

        $this->assertTrue(file_exists($copiedFile));

        @unlink($copiedFile);

        $client->removeDir(self::$testDir);
    }

    public function testFind() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createFile(self::$testFile);

        $this->assertNotEmpty($client->find('/.*\.txt$/i', dirname(self::$testFile)));

        $client->removeFile(self::$testFile);
    }

    public function testFindRecursive() : void
    {
        $this->markTestIncomplete('The find method with recursive approach seems to be problematic.');

        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $deepDir = self::$testDir . '/' . basename(self::$testDir);

        $client->createDir($deepDir);
        $client->createFile($deepDir . '/' . basename(self::$testFile));

        $this->assertNotEmpty($client->find('/.*\.txt$/i', self::$testDir, true));

        $client->removeDir(self::$testDir);
    }

    public function testCopyWithFile() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $client->createDir(self::$testDir);
        $client->createFile(self::$testFile);

        $this->assertTrue($client->copy(self::$testFile, self::$testDir));
        $this->assertTrue($client->isFile(self::$testDir . "/" . basename(self::$testFile)));

        $client->removeFile(self::$testFile);
        $client->removeDir(self::$testDir);
    }

    public function testCopyWithDirectory() : void
    {
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

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
        $factory = new FtpConnectionFactory();
        $client = new FtpClient($factory->create());

        $testFile = self::$testFile;

        $client->createFile($testFile);

        $this->assertTrue($client->appendFile($testFile, 'hello world!'));

        $client->removeFile($testFile);
    }
}
