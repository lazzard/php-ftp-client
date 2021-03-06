<?php

/**
 * This file is part of the Lazzard/php-ftp-client package.
 *
 * (c) El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lazzard\FtpClient;

use Lazzard\FtpClient\Command\FtpCommand;
use Lazzard\FtpClient\Connection\ConnectionInterface;
use Lazzard\FtpClient\Exception\FtpClientException;

/**
 * Class FtpClient
 *
 * @since   1.0
 * @author  El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpClient
{
    /**
     * FtpClient predefined constants.
     */
    const FILE_DIR_TYPE = 0;
    const FILE_TYPE     = 2;
    const DIR_TYPE      = 1;

    /** @var ConnectionInterface */
    protected $connection;

    /** @var FtpCommand */
    protected $command;

    /** @var FtpWrapper */
    protected $wrapper;

    /**
     * FtpClient constructor.
     *
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
        $this->command    = new FtpCommand($connection);
        $this->wrapper    = new FtpWrapper($connection);
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param FtpWrapper $wrapper
     */
    public function setWrapper($wrapper)
    {
        $this->wrapper = $wrapper;
    }

    /**
     * @param FtpCommand $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * Gets parent directory of the current working directory.
     *
     * @return string
     *
     * @throws FtpClientException
     */
    public function getParent()
    {
        $originalDir = $this->getCurrentDir();

        $this->back();

        $parent = $this->getCurrentDir();

        $this->changeDir($originalDir);

        if ($parent !== '/') {
            return substr($parent, 1);
        }

        return $parent;
    }

    /**
     * Gets current working directory.
     *
     * @return string
     *
     * @throws FtpClientException
     */
    public function getCurrentDir()
    {
        if (!$dir = $this->wrapper->pwd()) {
            throw new FtpClientException($this->wrapper->getErrorMessage()
                ?: "Unable to get the current working directory.");
        }

        return $dir;
    }

    /**
     * Back to the parent directory.
     *
     * @return bool
     *
     * @throws FtpClientException
     */
    public function back()
    {
        if (!$this->wrapper->cdup()) {
            throw new FtpClientException($this->wrapper->getErrorMessage() ?:
                "Unable to change to the parent directory.");
        }

        return true;
    }

    /**
     * Changes current working directory to the specified directory.
     *
     * @param string $directory The remote file directory.
     *
     * @return true Returns true in success, false otherwise.
     *
     * @throws FtpClientException
     */
    public function changeDir($directory)
    {
        if (!$this->wrapper->chdir($directory)) {
            throw new FtpClientException($this->wrapper->getErrorMessage()
                ?: "Unable to change the current directory to [{$directory}].");
        }

        return true;
    }

    /**
     * Checks whether if the giving file is a directory or not.
     *
     * @param string $remoteFile The remote file path.
     *
     * @return bool Returns true if the giving file is a directory type, false if
     *              is a file type or doesn't exist.
     *
     * @throws FtpClientException
     */
    public function isDir($remoteFile)
    {
        if (($list = $this->listDirDetails($this->dirname($remoteFile))) === false
            || !array_key_exists($remoteFile, $list)
            || ($type = $list[$remoteFile]['type']) === '') {
            return false;
        }

        return $type === 'dir';
    }

    /**
     * Checks if the giving file is a regular file.
     *
     * @param string $remoteFile The remote file path.
     *
     * @return bool Returns true if the giving remote file is a regular file, false if
     *              is a directory type or does not exists.
     *
     * @throws FtpClientException
     */
    public function isFile($remoteFile)
    {
        return !$this->isDir($remoteFile);
    }

    /**
     * Gets files count in the giving directory.
     *
     * @see FtpClient::listDirDetails()
     *
     * @param string $directory  The remote directory.
     * @param bool   $recursive  [optional] Whether to count the files recursively or not.
     * @param int    $filter     [optional] Specifies the files type to count.
     * @param bool   $ignoreDots [optional] Whether to ignore dots files.
     *
     * @return int Returns the files count as an integer.
     *
     * @throws FtpClientException
     */
    public function getCount($directory, $recursive = false, $filter = self::FILE_DIR_TYPE, $ignoreDots = true)
    {
        return count($this->listDirDetails(
            $directory,
            $recursive,
            $filter,
            $ignoreDots
        ));
    }

    /**
     * Gets detailed list of the files in the giving directory.
     *
     * Returned file information : ['name', 'chmod', 'num', 'owner', 'group', 'size', 'month', 'day', 'time', 'type',
     * 'path'].
     *
     * @param string $directory  The remote directory path.
     * @param bool   $recursive  [optional] Recursive listing option sets to false by default.
     * @param int    $filter     [optional] Specifies the type of the returned files, the default is
     *                           {@link FtpClient::FILE_DIR_TYPE} for files only or dirs only use
     *                           {@link FtpClient::FILE_TYPE} and {@link FtpClient::DIR_TYPE}.
     * @param bool   $ignoreDots [optional] Ignore dots files ['.', '..'], default sets to true.
     *
     * @return array Returns a detailed list of the files in the giving directory.
     *
     * @throws FtpClientException
     */
    public function listDirDetails(
        $directory,
        $recursive = false,
        $filter = self::FILE_DIR_TYPE,
        $ignoreDots = true
    ) {
        $escapedDir = str_replace(' ', '\ ', $directory);
        if (!($details = $this->wrapper->rawlist($escapedDir, $recursive))) {
            throw new FtpClientException($this->wrapper->getErrorMessage()
                ?: "Unable to get files list for [{$directory}] directory.");
        }

        $pathTmp = null;
        $info    = [];
        foreach ($details as $detail) {
            $chunks = preg_split('/[\s]+/', $detail, 9);
            // catch directory path
            if (strlen($chunks[0]) !== 0 && count($chunks) < 8) {
                $pathTmp = substr($detail, 0, -1);
                $pathTmp = preg_replace('/(\/\/)/', '/', $pathTmp);
            }

            if (count($chunks) === 9) {
                $type     = $this->chmodToFileType($chunks[0]);
                $filename = $chunks[8];

                if ($filter === self::FILE_TYPE && $type === 'dir'
                    || $filter === self::DIR_TYPE && $type !== 'dir'
                    || $ignoreDots && in_array($filename, ['.', '..'])) {
                    continue;
                }

                if (!$pathTmp) {
                    $path = $escapedDir !== '/' && $escapedDir
                        ? "$escapedDir/$filename" : $filename;
                } else {
                    $path = "$pathTmp/$filename";
                }

                $info[$path] = [
                    'chmod' => $chunks[0],
                    'num'   => $chunks[1],
                    'owner' => $chunks[2],
                    'group' => $chunks[3],
                    'size'  => $chunks[4],
                    'month' => $chunks[5],
                    'day'   => $chunks[6],
                    'time'  => $chunks[7],
                    'name'  => $filename,
                    'type'  => $type,
                    'path'  => $path
                ];
            }
        }

        return $info;
    }

    /**
     * Gets operating system type of the FTP server.
     *
     * @return string
     *
     * @throws FtpClientException
     */
    public function getSystem()
    {
        if (!($sysType = $this->wrapper->systype())) {
            throw new FtpClientException($this->wrapper->getErrorMessage()
                ?: "Unable to get FTP server operating system type.");
        }

        return $sysType;
    }

    /**
     * Gets the default transfer type on FTP server.
     *
     * @see FtpCommand::raw()
     *
     * @return string
     *
     * @throws FtpClientException
     */
    public function getTransferType()
    {
        if (!$response = $this->command->raw("SYST")) {
            throw new FtpClientException($response['message']);
        }

        return explode(' ', $response['message'], 3)[2];
    }

    /**
     * Deletes a regular remote file on the server.
     *
     * @param string $remoteFile The remote file path.
     *
     * @return bool Returns true if the giving file was successfully removed, otherwise an exception throws.
     *
     * @throws FtpClientException
     */
    public function removeFile($remoteFile)
    {
        if (!$this->wrapper->delete($remoteFile)) {
            throw new FtpClientException($this->wrapper->getErrorMessage()
                ?: "Unable to delete the file [{$remoteFile}].");
        }

        return true;
    }

    /**
     * Checks whether the giving file/directory exists.
     *
     * @param string $remoteFile The remote file path.
     *
     * @return bool Returns true if the remote file exists, false otherwise.
     */
    public function isExists($remoteFile)
    {
        // Trying to get the files list of the remote file parent directory, this check
        // is basically to avoid passing false to the next 'in_array' function
        // below, so we don't want to get an error because of this.
        if (!$list = $this->wrapper->nlist($this->dirname($remoteFile))) {
            return false;
        }

        return in_array(basename($remoteFile), $list);
    }

    /**
     * Deletes a directory on the FTP server.
     *
     * Note! This method will removes everything within the giving directory.
     *
     * @param string $directory The remote directory path.
     *
     * @return bool Returns true is success, false otherwise.
     *
     * @throws FtpClientException
     */
    public function removeDir($directory)
    {
        $list = array_reverse($this->listDirDetails($directory, true));
        
        foreach ($list as $fileInfo) {
            if ($fileInfo['type'] === 'file') {
                $this->wrapper->delete($fileInfo['path']);
                continue;
            }
            $this->wrapper->rmdir($fileInfo['path']);
        }

        return $this->wrapper->rmdir($directory);
    }

    /**
     * Gets last modified time of a remote file.
     *
     * Note: this method not working with directories.
     *
     * @param string      $remoteFile The remote file name.
     * @param string|null $format     [optional] A date format string to be passed to {@link date()} function.
     *
     * @return string|int Returns the string format if the format parameter was
     *                    specified, if not returns a numeric timestamp representation.
     *
     * @throws FtpClientException
     */
    public function lastMTime($remoteFile, $format = null)
    {
        if (!$time = $this->wrapper->mdtm($remoteFile)) {
            throw new FtpClientException($this->wrapper->getErrorMessage()
                ?: "Could not get last modified time for [{$remoteFile}].");
        }

        return $format ? date($format, $time) : $time;
    }

    /**
     * Determines if the giving feature is supported by the remote server or not.
     *
     * Note! the characters case are not important.
     *
     * @see FtpClient::getFeatures()
     *
     * @param string $feature
     *
     * @return bool Returns true if the feature is supported, false otherwise.
     */
    public function isFeatureSupported($feature)
    {
        return in_array(
            strtolower($feature),
            array_map('strtolower', $this->getFeatures())
        );
    }

    /**
     * Gets additional commands supported by the FTP server outside the basic commands defined in RFC959.
     *
     * @link https://tools.ietf.org/html/rfc959
     *
     * @see FtpCommand::raw()
     *
     * @return array Returns remote features in array.
     */
    public function getFeatures()
    {
        return array_map('ltrim', $this->command->raw("FEAT")['body']);
    }

    /**
     * Gets remote directory size.
     *
     * @param string $directory The remote directory path.
     *
     * @return int Return the size in bytes.
     *
     * @throws FtpClientException
     */
    public function dirSize($directory)
    {
        return array_sum(
            array_column($this->listDirDetails(
                $directory,
                true,
                self::DIR_TYPE
            ), 'size')
        );
    }

    /**
     * Gets list of files names in the giving directory.
     *
     * @param string $directory  The remote directory path.
     * @param int    $filter     [optional] Specifies the type of the returned files, the default is
     *                           {@link FtpClient::FILE_DIR_TYPE} for files only or dirs only use
     *                           {@link FtpClient::FILE_TYPE} and {@link FtpClient::DIR_TYPE}.
     * @param bool   $ignoreDots [optional] Ignore dots files ['.', '..'], default sets to false.
     *
     * @return array returns a list of files names as an array.
     *
     * @throws FtpClientException
     */
    public function listDir($directory, $filter = self::FILE_DIR_TYPE, $ignoreDots = true)
    {
        if (!$files = $this->wrapper->nlist($directory)) {
            throw new FtpClientException($this->wrapper->getErrorMessage()
                ?: "Failed to get files list.");
        }

        if ($ignoreDots) {
            $files = array_slice($files, 2);
        }

        switch ($filter) {
            case self::DIR_TYPE:
                return array_filter($files, function ($file) {
                    return $this->isDir($file);
                });

            case self::FILE_TYPE:
                return array_filter($files, function ($file) {
                    return !$this->isDir($file);
                });

            default:
                return $files;
        }
    }

    /**
     * Checks if the remote file/directory is empty or not.
     *
     * @param string $remoteFile The remote file path.
     *
     * @return bool Returns true if empty, otherwise returns false.
     *
     * @throws FtpClientException
     */
    public function isEmpty($remoteFile)
    {
        if ($this->isDir($remoteFile)) {
            return empty($this->listDir($remoteFile));
        }

        return $this->fileSize($remoteFile) === 0;
    }

    /**
     * Gets a regular remote file size.
     *
     * @param string $remoteFile The remote file path.
     *
     * @return int Return the size in bytes.
     *
     * @throws FtpClientException
     */
    public function fileSize($remoteFile)
    {
        /**
         * 'SIZE' command is not a standardized in the basic FTP protocol as defined in RFC 959, therefore
         * many FTP servers may not implement this command, to work around this we use the listDirDetails()
         * method which uses the ftp_rawlist FTP extension function, in turn this function uses the LIST command
         * to get the directory files information includes the files size.
         *
         * @link https://tools.ietf.org/html/rfc959
         */
        if (!$this->isFeatureSupported('SIZE')) {
            $list = $this->listDirDetails('/');
            foreach (range(0, count($list) - 1) as $i) {
                if ($list[$i]['name'] === $remoteFile) {
                    return (int)$list[$i]['size'];
                }
            }
        }

        return $this->wrapper->size($remoteFile);
    }

    /**
     * Moves a remote file/directory to another path.
     *
     * @param string $source            The remote file to be moved.
     * @param string $destinationFolder The destination remote directory.
     *
     * @return bool Returns true in success, an exception throws otherwise.
     * @throws FtpClientException
     */
    public function move($source, $destinationFolder)
    {
        return $this->rename($source, "$destinationFolder/" . basename($source));
    }

    /**
     * Renames a remote file/directory.
     *
     * @param string $remoteFile The remote file to renames.
     * @param string $newName    The new name.
     *
     * @return bool Returns true in success, otherwise an exception throws.
     *
     * @throws FtpClientException
     */
    public function rename($remoteFile, $newName)
    {
        if (!$this->wrapper->rename($remoteFile, $newName)) {
            throw new FtpClientException($this->wrapper->getErrorMessage()
                ?: sprintf(
                    "Unable to rename %s to %s",
                    $remoteFile,
                    $newName
                ));
        }

        return true;
    }

    /**
     * Sends a request to the server to keep the control channel alive and prevent the server from
     * disconnecting the session.
     *
     * @see FtpCommand::raw()
     *
     * @return bool Return true in success, false otherwise.
     */
    public function keepAlive()
    {
        return $this->command->raw("NOOP")['success'];
    }

    /**
     * Sends a request to FTP server to allocate a space for the next file transfer.
     *
     * Note! this function can success even the FTP server doesn't requires
     * the allocating spaces for the file transfers.
     *
     * @param int An integer represent the size in bytes
     *
     * @return bool
     *
     * @throws FtpClientException
     */
    public function allocateSpace($bytes)
    {
        if (!is_int($bytes)) {
            throw new FtpClientException("[{$bytes}] must be of type integer.");
        }

        if (!$this->wrapper->alloc($bytes)) {
            throw new FtpClientException($this->wrapper->getErrorMessage()
                ?: "Unable to allocate [{$bytes}] bytes on the server.");
        }

        return true;
    }

    /**
     * Starts downloading a remote file.
     *
     * Note! this method download the file synchronously (blocking mode),
     * for the async operations use {@link FtpClient::asyncDownload()} or
     * {@link FtpClient::asyncUpload()} methods.
     *
     * @param string $remoteFile The remote file to download.
     * @param int    $localFile  The local file path.
     * @param bool   $resume     [optional] resume downloading the file, the default is true.
     * @param int    $mode       [optional] The mode which will be used to transfer the file, the default is
     *                           the binary mode, if you don't know which mode you can use
     *                           {@link FtpClient::getTransferMode()}.
     *
     * @return bool Return true in success, otherwise an exception throws.
     *
     * @throws FtpClientException
     */
    public function download($remoteFile, $localFile, $resume = true, $mode = FtpWrapper::BINARY)
    {
        $startPos = 0;
        if ($resume && file_exists($localFile) && $size = @filesize($localFile)) {
            $startPos = $size;
        }

        if (!$this->wrapper->get($localFile, $remoteFile, $mode, $startPos)) {
            throw new FtpClientException($this->wrapper->getErrorMessage()
                ?: "Unable to retrieve the file [{$remoteFile}].");
        }

        return true;
    }

    /**
     * Retrieves a remote file asynchronously (non-blocking).
     *
     * @param string   $remoteFile The remote file to download.
     * @param string   $localFile  The local file path.
     * @param callable $callback   A callback function performed asynchronously while downloading the remote file.
     * @param bool     $resume     [optional] resume downloading the file, the default is true.
     * @param int      $interval   [optional] An optional parameter represent the interval in seconds that the
     *                             callback function will repeatedly called every specific interval until the
     *                             transfer is complete, the default value sets to 1 seconds.
     * @param int      $mode       [optional] The mode which will be used to transfer the file, the default is
     *                             the binary mode, if you don't know which mode you can use
     *                             {@link FtpClient::getTransferMode()}.
     *
     * @return bool Return bool if the transfer operation was successfully complete, if somethings goes wrong during
     *              the transfer an exception throws.
     *
     * @throws FtpClientException
     */
    public function asyncDownload(
        $remoteFile,
        $localFile,
        $callback,
        $resume = true,
        $interval = 1,
        $mode = FtpWrapper::BINARY
    ) {
        $startPos = 0;
        if ($resume && file_exists($localFile) && $size = @filesize($localFile)) {
            $startPos = $size;
        }

        $download       = $this->wrapper->nb_get($localFile, $remoteFile, $mode, $startPos);
        $remoteFileSize = $this->fileSize($remoteFile);
        $startTime      = microtime(true);
        $sizeTmp        = $startPos;
        $elapsedTimeTmp = 0;
        while ($download === FtpWrapper::MOREDATA) {
            $download    = $this->wrapper->nb_continue();
            $elapsedTime = ceil(microtime(true) - $startTime);

            // The first condition : perform the callback function only once every interval time.
            // The second one      : perform the callback function every interval time.
            // The integer cast inside the is_int in the second condition is because
            // of the '$elapsedTime' is a float number.

            // This is a small simulation of the first 2 seconds and supposing the interval is sets to 1s :
            // Time(0.5s)  : (0 !== 1 && is_int( (int) 0.5f  / 1) => false
            // Time(1.01s) : (1 !== 2 && is_int( (int) 1.01f / 1) => true
            // Time(1.5s)  : (2 !== 2 && is_int( (int) 1.5f  / 1) => false
            // Time(2s)    : (2 !== 2 && is_int( (int) 2f    / 1) => false
            // Time(2.01s) : (2 !== 3 && is_int( (int) 2.01f / 1) => true
            if ($elapsedTimeTmp !== $elapsedTime && is_int((int)$elapsedTime / $interval)) {
                clearstatcache();
                $localFileSize = filesize($localFile);

                $callback([
                    'speed'       => $this->transferSpeed($localFileSize - $startPos, $elapsedTime),
                    'percentage'  => $this->transferPercentage($localFileSize, $remoteFileSize),
                    'transferred' => $this->transferredBytes($localFileSize, $sizeTmp),
                    'seconds'     => $elapsedTime
                ]);
                
                $sizeTmp = $localFileSize;
            }

            $elapsedTimeTmp = $elapsedTime;
        }

        if ($download === FtpWrapper::FAILED) {
            throw new FtpClientException("Downloading the file [{$remoteFile}] was failed.");
        }

        return (bool)FtpWrapper::FINISHED;
    }

    /**
     * Reads the remote file content and returns the data as a string.
     *
     * @param string $remoteFile
     *
     * @return string|false Returns the file content as a string, if the passed FTP
     *                      file is not a regular file then a false returned.
     * @throws FtpClientException
     */
    public function getFileContent($remoteFile)
    {
        if (!$this->isFile($remoteFile)) {
            return false;
        }

        // Create a temporary file in the system temp
        $tempFile = tempnam(sys_get_temp_dir(), $remoteFile);
        if (!$this->wrapper->get($tempFile, $remoteFile, FtpWrapper::ASCII)) {
            throw new FtpClientException($this->wrapper->getErrorMessage()
                ?: "Unable to get [{$remoteFile}] content.");
        }

        $content = file_get_contents($tempFile);
        unlink($tempFile); // delete the temp file

        return $content;
    }

    /**
     * Creates an FTP file.
     *
     * @param string     $filename
     * @param mixed|null $content
     * @param int        $mode
     *
     * @return bool
     * @throws FtpClientException
     */
    public function createFile($filename, $content = null, $mode = FtpWrapper::BINARY)
    {
        // Create a file pointer to a temp file
        $handle = fopen('php://temp', 'a');
        fwrite($handle, (string)$content);
        rewind($handle); // Rewind position

        if (!$this->wrapper->fput($filename, $handle, $mode)) {
            throw new FtpClientException($this->wrapper->getErrorMessage() ?:
                "Failed to create file [{$filename}] on the server.");
        }

        return true;
    }

    /**
     * Creates a directory on the FTP server.
     *
     * Note! this method supports the recursive directory creation.
     *
     * @param string $directory The directory name or the full path to create the dirs recursively.
     *                          Ex : 'foo/bar/java/'.
     *
     * @return bool Returns true in success, false otherwise.
     */
    public function createDir($directory)
    {
        $dirs      = explode('/', $directory);
        $dirsCount = count($dirs);

        if ($dirsCount === 0) {
            return $this->wrapper->mkdir($directory);
        }

        for ($i = 1; $i <= $dirsCount; $i++) {
            $dir = join('/', array_slice($dirs, 0, $i));
            !$this->isExists($dir) && !$this->wrapper->mkdir($dir);
        }

        return true;
    }

    /**
     * Starts uploading the giving local file to the FTP server.
     *
     * @param string|resource $localFile  The local file to upload.
     * @param string          $remoteFile The remote file to upload data into.
     * @param bool            $resume     [optional] Specifies whether to resume the upload operation.
     * @param int             $mode       [optional] Specifies the transfer mode.
     *
     * @return bool
     *
     * @throws FtpClientException
     */
    public function upload($localFile, $remoteFile, $resume = true, $mode = FtpWrapper::BINARY)
    {
        $startPos = 0;
        if ($resume && $this->isExists($remoteFile)) {
            $startPos = $this->fileSize($remoteFile);
        }

        if (!$this->wrapper->put($remoteFile, $localFile, $mode, $startPos)) {
            throw new FtpClientException($this->wrapper->getErrorMessage()
                ?: "Unable to upload the file [{$localFile}].");
        }

        return true;
    }

    /**
     * Uploading a local file asynchronously to the remote server.
     *
     * @param string   $localFile  The local file to upload.
     * @param string   $remoteFile The remote file path.
     * @param callable $callback   A callback function performed asynchronously while uploading the remote file.
     * @param bool     $resume     [optional] resume downloading the file, the default is true.
     * @param int      $interval   [optional] An optional parameter represent the interval in seconds that the
     *                             callback function will repeatedly called every specific interval until the
     *                             transfer is complete, the default value sets to 1 seconds.
     * @param int      $mode       [optional] The mode which will be used to transfer the file, the default is
     *                             the binary mode, if you don't know which mode you can use
     *                             {@link FtpClient::getTransferMode()}.
     *
     * @return bool Return true if file successfully uploaded, if not an exception throws.
     *
     * @throws FtpClientException
     */
    public function asyncUpload(
        $localFile,
        $remoteFile,
        $callback,
        $resume = true,
        $interval = 1,
        $mode = FtpWrapper::BINARY
    ) {
        $startPos = 0;
        if ($resume && $this->isExists($remoteFile)) {
            $startPos = $this->fileSize($remoteFile);
        }

        // To check asynchronously the uploading state we use the ftp_nb_fput function instead
        // of ftp_nb_put, by passing the local file pointer to this function we will
        // be able to know the remote file size every time using the ftell function.
        $handle = fopen($localFile, 'r');
        $download = $this->wrapper->nb_fput(
            $remoteFile,
            $handle,
            $mode,
            $startPos
        );

        $localFileSize  = filesize($localFile);
        $startTime      = microtime(true);
        $sizeTmp        = null;
        $elapsedTimeTmp = null;
        while ($download === FtpWrapper::MOREDATA) {
            $download    = $this->wrapper->nb_continue();
            $elapsedTime = ceil(microtime(true) - $startTime);

            if ($elapsedTimeTmp !== $elapsedTime && is_int((int)$elapsedTime / $interval)) {
                $remoteFileSize = ftell($handle);

                $callback([
                    'speed'       => $this->transferSpeed($remoteFileSize - $startPos, $elapsedTime),
                    'percentage'  => $this->transferPercentage($remoteFileSize, $localFileSize),
                    'transferred' => $this->transferredBytes($remoteFileSize, $sizeTmp),
                    'seconds'     => $elapsedTime
                ]);

                $sizeTmp = $remoteFileSize;
            }

            $elapsedTimeTmp = $elapsedTime;
        }

        if ($download === FtpWrapper::FAILED) {
            throw new FtpClientException("Failed to upload the file [{$localFile}].");
        }

        return (bool)FtpWrapper::FINISHED;
    }

    /**
     * Sets permissions on FTP file or directory.
     *
     * @param string           $filename The remote file name.
     * @param array|int|string $mode     The mode parameter can be an integer or a string contains three digits e.g
     *                                   (777). The array parameter must be an associative array where a key is the
     *                                   permission group ['owner', 'group', 'other'] and the value is a string
     *                                   representation separated by a '-' contains the permissions to be sets on the
     *                                   remote file e.g "w-r-e".
     *
     * An example : [
     * 'owner' => 'r-w',
     * 'group' => 'e',
     * 'other' => 'w-r'
     * ]
     *
     * @return bool
     *
     * @throws FtpClientException
     */
    public function setPermissions($filename, $mode)
    {
        if (is_array($mode)) {
            foreach ($mode as $key => $value) {
                if (!in_array($key, ['owner', 'group', 'other'])) {
                    throw new FtpClientException("[{$key}] is invalid permission group.");
                }
            }

            $o = '0'; // owner
            $g = '0'; // group
            $w = '0'; // world

            foreach ($mode as $key => $value) {
                switch ($key) {
                    case "other":
                        $w = $this->chmodToNumeric($value);
                        break;
                    case "owner":
                        $o = $this->chmodToNumeric($value);
                        break;
                    case "group":
                        $g = $this->chmodToNumeric($value);
                        break;
                }
            }

            $mode = sprintf("%s%s%s", $o, $g, $w);
        }

        $mode = octdec(str_pad($mode, 4, '0', STR_PAD_LEFT));

        if (!$this->wrapper->chmod($mode, $filename)) {
            throw new FtpClientException($this->wrapper->getErrorMessage()
                ?: "Failed to set permissions to [{$filename}]");
        }

        return true;
    }

    /**
     * Copy a local file/directory to the remote server.
     *
     * @param string $source            The path of the source file/directory.
     * @param string $destinationFolder The remote destination folder.
     *
     * @return bool
     *
     * @throws FtpClientException
     */
    public function copyFromLocal($source, $destinationFolder)
    {
        // get the base name of the source (the filename without the path).
        $sourceBase = basename($source);

        // remove the slashes if founded from $destinationFolder to prevent any issues after.
        $destinationFolder = trim($destinationFolder, '/');

        // if the source is a file.
        if (is_file($source)) {
            $remotePath = "$destinationFolder/$sourceBase";
            return $this->upload($source, $remotePath);
        }

        // handle if the giving source is a directory.
        if (is_dir($source) && is_readable($source)) {
            $destinationFolder = "$destinationFolder/$sourceBase";
            if ($this->createDir($destinationFolder)) {
                foreach (scandir($source) as $file) {
                    if (in_array($file, ['.', '..'])) continue;
                    $this->copyFromLocal("$source/$file", $destinationFolder);
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Copies a remote file/directory to a local machine.
     *
     * @param string $remoteSource      The remote path of the source file/directory.
     * @param string $destinationFolder The local destination folder.
     *
     * @return bool
     *
     * @throws FtpClientException
     */
    public function copyToLocal($remoteSource, $destinationFolder)
    {
        $sourceBase        = basename($remoteSource);
        $destinationFolder = trim($destinationFolder, '/');

        if ($this->isFile($remoteSource)) {
            $localPath = "$destinationFolder/$sourceBase";
            return $this->download($remoteSource, $localPath, false);
        }

        if ($this->isDir($remoteSource)) {
            $destinationFolder = "$destinationFolder/$sourceBase";

            if (!file_exists($destinationFolder) && !@mkdir($destinationFolder, 0777, true)) {
                throw new FtpClientException(error_get_last()['message']);
            }

            $files = $this->listDirDetails($remoteSource, true);
            foreach ($files as $file) {
                if (preg_match('/' . preg_quote($remoteSource, '/') . '\/(.*)/', $file['path'], $matches) !== false) {
                    $source = dirname($matches[1]);
                    $this->copyToLocal($file['path'], "$destinationFolder/$source");
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Copies a remote file/dir to another directory.
     *
     * @param string $remoteSource
     * @param string $remoteDirectory
     *
     * @return bool Returns true in success and false otherwise, an exception may
     *              throws also.
     *
     * @throws FtpClientException
     */
    public function copy($remoteSource, $remoteDirectory)
    {
        $remoteDestination = "$remoteDirectory/" . basename($remoteSource);

        if ($this->isFile($remoteSource)) {
            $tempFile = tempnam(sys_get_temp_dir(), $remoteSource);
            if ($tempFile !== false && file_put_contents($tempFile, $this->getFileContent($remoteSource)) !== false) {
                try {
                    return $this->upload($tempFile, $remoteDestination);
                } finally {
                    unlink($tempFile);
                }
            }
        }

        if ($this->isDir($remoteSource) && $this->createDir($remoteDestination)) {
            $files = $this->listDirDetails($remoteSource, true);
            foreach ($files as $name => $info) {
                $newPath = $remoteDestination . str_replace($remoteSource, '', $info['path']);

                if (!$info['type'] === 'file') {
                    $this->createDir($newPath);
                } else {
                    $this->copy($info['path'], $this->dirname($newPath));
                }
            }
        }

        return true;
    }

    /**
     * Finds a remote file/directory
     *
     * @param string $pattern   The regex pattern.
     * @param string $directory The remote directory.
     * @param bool   $recursive
     *
     * @return array
     *
     * @throws FtpClientException
     */
    public function find($pattern, $directory, $recursive = false)
    {
        $list    = $this->listDirDetails($directory, $recursive);
        $files   = array_keys($list);
        $results = [];

        if (($matches = @preg_grep($pattern, $files)) === false) {
            throw new FtpClientException(sprintf("Invalid regex pattern given to %s() : %s",
                __METHOD__,
                error_get_last()['message']
            ));
        }

        foreach ($matches as $match) {
            $results[] = $list[$match];
        }

        return $results;
    }

    /**
     * Gets the transfer operation average speed.
     *
     * @param int $size
     * @param int $elapsedTime
     *
     * @return float
     */
    protected function transferSpeed($size, $elapsedTime)
    {
        return (float)number_format(($size / $elapsedTime) / 1000, 2);
    }

    /**
     * Gets the transfer operation progress percentage.
     *
     * @param int $size
     * @param int $totalSize
     *
     * @return int
     */
    protected function transferPercentage($size, $totalSize)
    {
        return (int)(($size * 100) / $totalSize);
    }

    /**
     * Gets the amount of bytes transferred in a transfer operation.
     *
     * @param int $size
     * @param int $previousSize
     *
     * @return int
     */
    protected function transferredBytes($size, $previousSize)
    {
        return (int)(($size - $previousSize) / 1000);
    }

    /**
     * Gets the file type (type, dir, link) from teh giving chmod string
     * Ex : ('drwxr-xr-x' => 'dir').
     *
     * @param string $chmod
     *
     * @return string
     */
    protected function chmodToFileType($chmod)
    {
        switch ($chmod[0]) {
            case '-':
                return 'file';

            case 'd':
                return 'dir';

            case 'l':
                return 'link';

            default:
                return '';
        }
    }

    /**
     * Converts the giving chmod string to a numeric representation.
     * Ex : "w-r-e" => 7
     * Ex : "r-e"   => 3
     *
     * @param string $chmod
     *
     * @return int
     */
    protected function chmodToNumeric($chmod)
    {
        $actions = ['r' => 4, 'w' => 2, 'e' => 1];
        $chunks  = explode('-', $chmod);
        $numeric = 0;

        foreach ($chunks as $action) {
            $numeric += $actions[$action];
        }

        return $numeric;
    }

    /**
     * Gives the valid parent directory of an FTP path.
     *
     * @param string $dirname
     *
     * @return string
     */
    protected function dirname($dirname)
    {
        // Fix dirname in windows which gives '\' instead of '/' if the path matches for example '/foo/'.
        return trim(dirname($dirname), '\\');
    }
}
