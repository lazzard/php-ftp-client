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
        $this->command = new FtpCommand($connection);
        $this->wrapper = new FtpWrapper($connection);
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
        $this->setCurrentDir($originalDir);

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
            throw new FtpClientException($this->wrapper->getFtpErrorMessage()
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
            throw new FtpClientException($this->wrapper->getFtpErrorMessage() ?:
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
    public function setCurrentDir($directory)
    {
        if (!$this->isDir($directory)) {
            throw new FtpClientException("[{$directory}] is not a directory.");
        }

        if (!$this->wrapper->chdir($directory)) {
            throw new FtpClientException($this->wrapper->getFtpErrorMessage()
                ?: "Unable to change the current directory to [{$directory}].");
        }

        return true;
    }

    /**
     * Checks whether if the giving file is a directory or not.
     *
     * @param string $remoteFile The remote file path.
     *
     * @return bool Returns true if the giving file is a directory or not exists, false if it
     *              a file or doesn't exists.
     */
    public function isDir($remoteFile)
    {
        try {
            return is_array($this->listDirectory($remoteFile));
        } catch (FtpClientException $e) {
            return false;
        }
    }

    /**
     * Checks if the giving file is a regular file.
     *
     * @param string $remoteFile The remote file path.
     *
     * @return bool Returns true if the giving remote file is a regular file, false if it
     *              a directory or doesn't exists.
     */
    public function isFile($remoteFile)
    {
        return $this->wrapper->size($remoteFile) !== -1;
    }

    /**
     * Gets files count in the giving directory.
     *
     * @see FtpClient::listDirectoryDetails()
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
        return count($this->listDirectoryDetails(
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
    public function listDirectoryDetails(
        $directory,
        $recursive = false,
        $filter = self::FILE_DIR_TYPE,
        $ignoreDots = true
    ) {
        if (!$this->isDir($directory)) {
            throw new FtpClientException("[{$directory}] is not a directory.");
        }

        if (!($details = $this->wrapper->rawlist(str_replace(' ', '\ ', $directory), $recursive))) {
            throw new FtpClientException($this->wrapper->getFtpErrorMessage()
                ?: "Unable to get files list for [{$directory}] directory.");
        }

        $pathTmp = null;
        $info    = [];
        foreach ($details as $detail) {
            $chunks = preg_split('/[\s]+/', $detail, 9);

            if (strlen($chunks[0]) !== 0 && count($chunks) < 8) { // catch directory path
                $pathTmp = substr($detail, 0, -1);
                // Fix the two slashes
                $pathTmp = preg_replace('/(\/\/)/', '/', $pathTmp);
            }

            if (count($chunks) === 9) {
                $type = $this->chmodToFileType($chunks[0]);

                if ($filter === self::FILE_TYPE) {
                    if ($type === 'dir') {
                        continue;
                    }
                } elseif ($filter === self::DIR_TYPE) {
                    if ($type !== 'dir') {
                        continue;
                    }
                }

                if ($ignoreDots) {
                    if (in_array($chunks[8], ['.', '..'])) {
                        continue;
                    }
                }

                if (!$pathTmp) {
                    $path = $directory !== '/' ? $directory . '/' . $chunks[8] : $chunks[8];
                } else {
                    $path = $pathTmp . '/' . $chunks[8];
                }

                $info[] = [
                    'name'  => $chunks[8],
                    'chmod' => $chunks[0],
                    'num'   => $chunks[1],
                    'owner' => $chunks[2],
                    'group' => $chunks[3],
                    'size'  => $chunks[4],
                    'month' => $chunks[5],
                    'day'   => $chunks[6],
                    'time'  => $chunks[7],
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
            throw new FtpClientException($this->wrapper->getFtpErrorMessage()
                ?: "Unable to get FTP server operating system type.");
        }

        return $sysType;
    }

    /**
     * Gets the default transfer type of the FTP server.
     *
     * @see FtpCommand::raw()
     *
     * @return string
     *
     * @throws FtpClientException
     */
    public function getDefaultTransferType()
    {
        if (!$response = $this->command->raw("SYST")) {
            throw new FtpClientException($response['message']);
        }

        return explode(' ', $response['message'], 3)[2];
    }

    /**
     * Deletes regular remote file.
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
            throw new FtpClientException($this->wrapper->getFtpErrorMessage()
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
        // The str_replace because of dirname in windows gives '\' instead of '/'
        // if the path matches for example '/foo/'.
        if (!$list = $this->wrapper->nlist(str_replace('\\', '/', dirname($remoteFile)))) {
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
    public function removeDirectory($directory)
    {
        $list = $this->listDirectoryDetails($directory, true);

        $_list = array_reverse($list);
        foreach ($_list as $fileInfo) {
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
        // 'MDTM' command is not a standardized in the basic FTP protocol as defined in RFC 959.
        if (!$this->isFeatureSupported('MDTM')) {
            throw new FtpClientException("This feature is not supported by the remote server.");
        }

        if (!$this->isFile($remoteFile)) {
            throw new FtpClientException(sprintf("%s::%s() does not work with directories.",
                self::class,
                __FUNCTION__));
        }

        if (!$time = $this->wrapper->mdtm($remoteFile)) {
            throw new FtpClientException($this->wrapper->getFtpErrorMessage()
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
        if (!$this->isDir($directory)) {
            throw new FtpClientException("[{$directory}] must be an existing directory.");
        }

        return array_sum(
            array_column($this->listDirectoryDetails(
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
    public function listDirectory($directory, $filter = self::FILE_DIR_TYPE, $ignoreDots = true)
    {
        if (!$files = $this->wrapper->nlist($directory)) {
            throw new FtpClientException($this->wrapper->getFtpErrorMessage()
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
        $this->throwIfNotExists($remoteFile);

        if ($this->isDir($remoteFile)) {
            return empty($this->listDirectory($remoteFile));
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
        if ($this->isDir($remoteFile)) {
            throw new FtpClientException("[{$remoteFile}] must be an existing file.");
        }

        /**
         * 'SIZE' command is not a standardized in the basic FTP protocol as defined in RFC 959, therefore
         * many FTP servers may not implement this command, to work around this we use the listDirectoryDetails()
         * method which uses the ftp_rawlist FTP extension function, in turn this function uses the LIST command
         * to get the directory files information includes the files size.
         *
         * @link https://tools.ietf.org/html/rfc959
         */

        if (!$this->isFeatureSupported('SIZE')) {
            $list = $this->listDirectoryDetails('/');
            foreach (range(0, count($list) - 1) as $i) {
                if ($list[$i]['name'] === $remoteFile) {
                    return (int)$list[$i]['size'];
                }
            }
        }

        return $this->wrapper->size($remoteFile);
    }

    /**
     * Moves a file or a directory to another path.
     *
     * @param string $source            The remote file to be moved.
     * @param string $destinationFolder The destination remote directory.
     *
     * @return bool Returns true in success, an exception throws otherwise.
     *
     * @throws FtpClientException
     */
    public function move($source, $destinationFolder)
    {
        $this->throwIfNotExists($source, "The source remote file [$source] must be exists to be moved.");

        if (!$this->isDir($destinationFolder)) {
            throw new FtpClientException("[{$destinationFolder}] must be an existing directory.");
        }

        return $this->rename($source, $destinationFolder . '/' . basename($source));
    }

    /**
     * Renames file/directory on the FTP server.
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
        $this->throwIfNotExists($remoteFile);

        // TODO Consider to remove this check
        if ($this->isExists($newName)) {
            throw new FtpClientException("[{$newName}] is already exists.");
        }

        if (!$this->wrapper->rename($remoteFile, $newName)) {
            throw new FtpClientException($this->wrapper->getFtpErrorMessage()
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
    public function keepConnectionAlive()
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
            throw new FtpClientException($this->wrapper->getFtpErrorMessage()
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
    public function download($remoteFile, $localFile, $resume = true, $mode = FTP_BINARY)
    {
        $this->throwIfNotExists($remoteFile);

        $startPos = 0;
        if ($resume) {
            // TODO filesize local file not exists.
            $startPos = filesize($localFile);
        }

        if (!$this->wrapper->get($localFile, $remoteFile, $mode, $startPos)) {
            throw new FtpClientException($this->wrapper->getFtpErrorMessage()
                ?: "Unable to retrieve the file [{$remoteFile}].");
        }

        return true;
    }

    /**
     * Retrieves a remote file asynchronously (non-blocking).
     *
     * @param string   $remoteFile         The remote file to download.
     * @param string   $localFile          The local file path.
     * @param callback $doWhileDownloading A callback function performed asynchronously while downloading the remote
     *                                     file.
     * @param bool     $resume             [optional] resume downloading the file, the default is true.
     * @param int      $interval           [optional] An optional parameter represent the interval in seconds that the
     *                                     callback function will repeatedly called every specific interval until the
     *                                     transfer is complete, the default value sets to 1 seconds.
     * @param int      $mode               [optional] The mode which will be used to transfer the file, the default is
     *                                     the binary mode, if you don't know which mode you can use
     *                                     {@link FtpClient::getTransferMode()}.
     *
     * @return bool Return bool if the transfer operation was successfully complete, if somethings goes wrong during
     *              the transfer an exception throws.
     *
     * @throws FtpClientException
     */
    public function asyncDownload(
        $remoteFile,
        $localFile,
        $doWhileDownloading,
        $resume = true,
        $interval = 1,
        $mode = FTP_BINARY
    ) {
        $this->throwIfNotExists($remoteFile);

        $startPos = 0;
        if ($resume && file_exists($localFile)) {
            clearstatcache();
            $startPos = filesize($localFile);
        }
        
        $remoteFileSize = $this->fileSize($remoteFile);
        
        $download = $this->wrapper->nb_get(
            $localFile,
            $remoteFile,
            $mode,
            $startPos
        );

        $startTime      = microtime(true);
        $sizeTmp        = $startPos;
        $elapsedTimeTmp = 0;
        while ($download === FTP_MOREDATA) {
            $download = $this->wrapper->nb_continue();

            $elapsedTime = ceil(microtime(true) - $startTime);

            // The first condition : perform the callback function only once every interval time.
            // The second one      : perform the callback function every interval time.
            // The integer cast inside the is_int in the second condition is because
            // of the '$elapsedTime' is a float number.
            // A small simulation of the first 2 seconds supposing the interval is sets to 1 :
            // Time(0.5s)  : (0 !== 1 && is_int( (int) 0.5f  / 1) => false
            // Time(1.01s) : (1 !== 2 && is_int( (int) 1.01f / 1) => true
            // Time(1.5s)  : (2 !== 2 && is_int( (int) 1.5f  / 1) => false
            // Time(2s)    : (2 !== 2 && is_int( (int) 2f    / 1) => false
            // Time(2.01s) : (2 !== 3 && is_int( (int) 2.01f / 1) => true
            if ($elapsedTimeTmp !== $elapsedTime && is_int((int)$elapsedTime / $interval)) {
                clearstatcache();
                $localFileSize = filesize($localFile);

                $doWhileDownloading([
                    'speed'       => $this->transferSpeed($localFileSize - $startPos, $elapsedTime),
                    'percentage'  => $this->transferPercentage($localFileSize, $remoteFileSize),
                    'transferred' => $this->transferredBytes($localFileSize, $sizeTmp),
                    'seconds'     => $elapsedTime
                ]);

                $sizeTmp = $localFileSize;
            }

            $elapsedTimeTmp = $elapsedTime;
        }

        if ($download === FTP_FAILED) {
            throw new FtpClientException("Downloading the file [{$remoteFile}] was failed.");
        }

        return (bool)FTP_FINISHED;
    }

    /**
     * Reads the remote file content and returns the data as a string.
     *
     * @param string $remoteFile
     *
     * @return string
     *
     * @throws FtpClientException
     */
    public function getFileContent($remoteFile)
    {
        if ($this->isDir($remoteFile)) {
            throw new FtpClientException("[{$remoteFile}] is a directory.");
        }

        // Create a temporary file in the system temp
        $tempFile = tempnam(sys_get_temp_dir(), $remoteFile);
        if (!$this->wrapper->get($tempFile, $remoteFile, FTP_ASCII)) {
            throw new FtpClientException($this->wrapper->getFtpErrorMessage()
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
    public function createFile($filename, $content = null, $mode = FTP_BINARY)
    {
        // Create a file pointer to a temp file
        $handle = fopen('php://temp', 'a');
        fwrite($handle, (string)$content);
        rewind($handle); // Rewind position

        if (!$this->wrapper->fput($filename, $handle, $mode)) {
            throw new FtpClientException($this->wrapper->getFtpErrorMessage() ?:
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
     *
     * @throws FtpClientException
     */
    public function createDirectory($directory)
    {
        if ($this->isExists($directory) && $this->isDir($directory)) {
            return true;
        }

        $dirs  = explode('/', $directory);
        $count = count($dirs);
        if ($count > 0) {
            for ($i = 1; $i <= $count; $i++) {
                $dir = join("/", array_slice($dirs, 0, $i));

                if (!$this->isExists($dir)) {
                    if (!$this->wrapper->mkdir($dir)) {
                        throw new FtpClientException($this->wrapper->getFtpErrorMessage()
                        ?: 'Unable to create directory ['.$dir.']');
                    }
                }
            }
            return true;
        }

        return $this->wrapper->mkdir($directory);
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
    public function upload($localFile, $remoteFile, $resume = true, $mode = FTP_BINARY)
    {
        if (!file_exists($localFile)) {
            throw new FtpClientException("Cannot uploading [{$localFile}] because is not exists.");
        }

        $startPos = 0;
        if ($resume && $this->isExists($remoteFile)) {
            $startPos = $this->fileSize($remoteFile);
        }

        if (!$this->wrapper->put($remoteFile, $localFile, $mode, $startPos)) {
            throw new FtpClientException($this->wrapper->getFtpErrorMessage()
                ?: "Unable to upload the file [{$localFile}].");
        }

        return true;
    }

    /**
     * Uploading a local file asynchronously to the remote server.
     *
     * @param string $localFile            The local file to upload.
     * @param string $remoteFile           The remote file path.
     * @param string $doWhileDownloading   A callback function performed asynchronously while downloading the remote
     *                                     file.
     * @param bool   $resume               [optional] resume downloading the file, the default is true.
     * @param int    $interval             [optional] An optional parameter represent the interval in seconds that the
     *                                     callback function will repeatedly called every specific interval until the
     *                                     transfer is complete, the default value sets to 1 seconds.
     * @param int    $mode                 [optional] The mode which will be used to transfer the file, the default is
     *                                     the binary mode, if you don't know which mode you can use
     *                                     {@link FtpClient::getTransferMode()}.
     *
     * @return bool Return true if file successfully uploaded, if not an exception throws.
     *
     * @throws FtpClientException
     */
    public function asyncUpload(
        $localFile,
        $remoteFile,
        $doWhileDownloading,
        $resume = true,
        $interval = 1,
        $mode = FTP_BINARY
    ) {
        if (!file_exists($localFile)) {
            throw new FtpClientException("[{$localFile}] doesn't exists to upload.");
        }

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
        while ($download === FTP_MOREDATA) {
            $download = $this->wrapper->nb_continue();

            $elapsedTime = ceil(microtime(true) - $startTime);

            if ($elapsedTimeTmp !== $elapsedTime && is_int((int)$elapsedTime / $interval)) {
                $remoteFileSize = ftell($handle);

                $doWhileDownloading([
                    'speed'       => $this->transferSpeed($remoteFileSize - $startPos, $elapsedTime),
                    'percentage'  => $this->transferPercentage($remoteFileSize, $localFileSize),
                    'transferred' => $this->transferredBytes($remoteFileSize, $sizeTmp),
                    'seconds'     => $elapsedTime
                ]);

                $sizeTmp = $remoteFileSize;
            }

            $elapsedTimeTmp = $elapsedTime;
        }

        if ($download === FTP_FAILED) {
            throw new FtpClientException("Failed to upload the file [{$localFile}].");
        }

        return (bool)FTP_FINISHED;
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
            throw new FtpClientException($this->wrapper->getFtpErrorMessage()
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
            $remotePath = $destinationFolder . "/$sourceBase";
            return $this->upload($source, $remotePath);
        }

        // handle if the giving source is a directory.
        if (is_dir($source) && is_readable($source)) {
            $destinationFolder = "$destinationFolder/$sourceBase";
            $this->createDirectory($destinationFolder);
            foreach (scandir($source) as $file) {
                if (in_array($file, ['.', '..'])) continue;
                $this->copyFromLocal($source . '/' . $file, $destinationFolder);
            }
            return true;
        }

        return false;
    }

    /**
     * @param string      $remoteFile
     * @param string|null $message
     *
     * @return void
     * @throws FtpClientException
     */
    protected function throwIfNotExists($remoteFile, $message = null)
    {
        if (!$this->isExists($remoteFile)) {
            throw new FtpClientException($message ?: "$remoteFile not exists on the server.");
        }
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
                return 'unknown file type.';
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
        $actions = [
            'r' => 4,
            'w' => 2,
            'e' => 1
        ];

        $chunks  = explode('-', $chmod);
        $numeric = 0;
        foreach ($chunks as $action) {
            $numeric += $actions[$action];
        }

        return $numeric;
    }
}
