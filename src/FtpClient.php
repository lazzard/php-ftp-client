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
use Lazzard\FtpClient\Exception\ClientException;

/**
 * Class FtpClient
 *
 * @since   1.0
 * @author  El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpClient
{
    /**
     * FtpClient predefined constants
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
     * Gets parent directory of the current working directory.
     *
     * @return string
     *
     * @throws ClientException
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
     */
    public function getCurrentDir()
    {
        return $this->wrapper->pwd();
    }

    /**
     * Back to the parent directory.
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function back()
    {
        if (!$this->wrapper->cdup()) {
            throw new ClientException(ClientException::getFtpServerError() ?:
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
     * @throws ClientException
     */
    public function setCurrentDir($directory)
    {
        if (!$this->isDir($directory)) {
            throw new ClientException("[{$directory}] is not a directory.");
        }

        if (!$this->wrapper->chdir($directory)) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Unable to change the current directory to [{$directory}].");
        }

        return true;
    }

    /**
     * Checks whether if the giving file is a directory or not.
     *
     * @param string $remoteFile The remote file path.
     *
     * @return bool Return true if the giving file is a directory,
     *              otherwise returns false.
     */
    public function isDir($remoteFile)
    {
        return ($this->wrapper->size($remoteFile) === -1);
    }

    /**
     * Checks if the giving file is a regular file.
     *
     * @param string $remoteFile The remote file path.
     *
     * @return bool Return true if the giving remote file is a regular file,
     *              otherwise returns false.
     */
    public function isFile($remoteFile)
    {
        return ($this->wrapper->size($remoteFile) !== -1);
    }

    /**
     * Gets files count in the giving directory.
     *
     * @see FtpClient::listDirectoryDetails()
     *
     * @param bool     $recursive  [optional]
     * @param int      $filter     [optional]
     * @param bool     $ignoreDots [optional]
     *
     * @param string $directory
     *
     * @return int Returns the files count as an integer.
     *
     * @throws ClientException
     */
    public function getCount($directory, $recursive = false, $filter = self::FILE_DIR_TYPE, $ignoreDots = false)
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
     * @param bool     $recursive  [optional] Recursive listing option sets to false by default.
     * @param int      $filter     [optional] Specifies the type of the returned files, the default is
     *                           {@link FtpClient::FILE_DIR_TYPE} for files only or dirs only use
     *                           {@link FtpClient::FILE_TYPE} and {@link FtpClient::DIR_TYPE}.
     * @param bool     $ignoreDots [optional] Ignore dots files ['.', '..'], default sets to false.
     *
     * @return array Returns a detailed list of the files in the giving directory.
     *
     * @throws ClientException
     */
    public function listDirectoryDetails(
        $directory,
        $recursive = false,
        $filter = self::FILE_DIR_TYPE,
        $ignoreDots = true
    ) {
        if (!$this->isDir($directory)) {
            throw new ClientException("[{$directory}] is not a directory.");
        }

        if (!($details = $this->wrapper->rawlist($directory, $recursive))) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Unable to get files list for [{$directory}] directory.");
        }

        $pathTmp = null;
        $info    = [];
        foreach ($details as $detail) {
            $chunks = preg_split('/\s+/', $detail);

            if (strlen($chunks[0]) !== 0 && count($chunks) !== 9) { // catch directory path
                $splice  = explode('/', substr($chunks[0], 0, -1));
                $pathTmp = join('/', $splice);
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
     * @throws ClientException
     */
    public function getSystem()
    {
        if (!($sysType = $this->wrapper->systype())) {
            throw new ClientException(ClientException::getFtpServerError()
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
     * @throws ClientException
     */
    public function getDefaultTransferType()
    {
        $response = $this->command->raw("SYST");

        if (!$response['success']) {
            throw new ClientException($response['message']);
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
     * @throws ClientException
     */
    public function removeFile($remoteFile)
    {
        if (!$this->isExists($remoteFile) || $this->isDir($remoteFile)) {
            throw new ClientException("[{$remoteFile}] must be an existing file.");
        }

        if (!$this->wrapper->delete($remoteFile)) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Unable to delete the file [{$remoteFile}].");
        }

        return true;
    }

    /**
     * Checks whether the giving file or directory exists.
     *
     * @param string $remoteFile The remote file path.
     *
     * @return bool Returns true if the remote file exists, false otherwise.
     */
    public function isExists($remoteFile)
    {
        return in_array(
            basename($remoteFile),
            $this->wrapper->nlist(dirname($remoteFile))
        );
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
     * @throws ClientException
     */
    public function removeDirectory($directory)
    {
        if (!$this->isDir($directory)) {
            throw new ClientException("[{$directory}] must be an existing directory.");
        }

        $list = $this->listDirectoryDetails($directory, true);

        $_list = array_reverse($list);

        foreach ($_list as $fileInfo) {
            if ($fileInfo['type'] === 'file') {
                $this->wrapper->delete($fileInfo['path']);
            } else {
                $this->wrapper->rmdir($fileInfo['path']);
            }
        }

        return $this->wrapper->rmdir($directory);
    }

    /**
     * Creates a directory on the FTP server.
     *
     * Note! this method supports the recursive directory creation.
     *
     * @param string $directory The directory name or the full path to create the dirs recursively.
     *                          Ex : 'foo/bar/java/'.
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function createDirectory($directory)
    {
        if ($this->isExists($directory)) {
            throw new ClientException("[{$directory}] already exists.");
        }

        $dirs  = explode('/', $directory);
        $count = count($dirs);

        for ($i = 1; $i <= $count; $i++) {
            $dir = join("/", array_slice($dirs, 0, $i));

            if (!$this->isDir($dir)) {
                $this->wrapper->mkdir($dir);
            }
        }

        return true;
    }

    /**
     * Gets last modified time of an FTP remote regular file.
     *
     * Note! this method not work for directories.
     *
     * @param string      $remoteFile The remote file name.
     * @param string|null $format     [optional] A date format string to be passed to {@link date()} function.
     *
     * @return string|int Returns the string format if the format parameter was
     *                    specified, if not returns a numeric timestamp representation.
     *
     * @throws ClientException
     */
    public function lastMTime($remoteFile, $format = null)
    {
        /**
         * MDTM command not a standard in the basic FTP protocol as defined in RFC 959.
         */
        if (!$this->isFeatureSupported('MDTM')) {
            throw new ClientException("This feature not supported by the remote server.");
        }

        if ($this->isDir($remoteFile)) {
            throw new ClientException("[$remoteFile] is not a regular file.");
        }

        if (!($time = $this->wrapper->mdtm($remoteFile))) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Could not get last modified time for [{$remoteFile}].");
        }

        return $format ? date($format, $time) : $time;
    }

    /**
     * Determines if the giving feature is supported by the remote server or not.
     *
     * Note! the characters case not important.
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
     * Gets the additional commands supported by the FTP server outside the basic commands
     * defined in RFC959.
     *
     * @link https://tools.ietf.org/html/rfc959
     *
     * @see  FtpCommand::raw()
     *
     * @return array|string Returns an array in success, if not the FTP reply error returned.
     */
    public function getFeatures()
    {
        $response = $this->command->raw("FEAT");

        if (!$response['success']) {
            return $response['message'];
        }

        return array_map('ltrim', $response['body']);
    }

    /**
     * Gets remote directory size.
     *
     * @param string $directory The remote directory path.
     *
     * @return int Return the size in bytes.
     *
     * @throws ClientException
     */
    public function dirSize($directory)
    {
        if (!$this->isDir($directory)) {
            throw new ClientException("[{$directory}] must be an existing directory.");
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
     * Checks whether if the giving directory is empty or not.
     *
     * @param string $directory The remote file directory.
     *
     * @return bool Returns true if empty, otherwise returns false.
     *
     * @throws ClientException
     */
    public function isEmptyDirectory($directory)
    {
        if (!$this->isDir($directory)) {
            throw new ClientException("[{$directory}] is not directory.");
        }

        return empty($this->listDirectory($directory));
    }

    /**
     * Gets list of files names in the giving directory.
     *
     * @param string $directory  The remote directory path.
     * @param int      $filter     [optional] Specifies the type of the returned files, the default is
     *                           {@link FtpClient::FILE_DIR_TYPE} for files only or dirs only use
     *                           {@link FtpClient::FILE_TYPE} and {@link FtpClient::DIR_TYPE}.
     * @param bool     $ignoreDots [optional] Ignore dots files ['.', '..'], default sets to false.
     *
     * @return array returns a list of files names as an array.
     *
     * @throws ClientException
     */
    public function listDirectory($directory, $filter = self::FILE_DIR_TYPE, $ignoreDots = true)
    {
        if (!$files = $this->wrapper->nlist($directory)) {
            throw new ClientException(ClientException::getFtpServerError()
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
                    return!$this->isDir($file);
                });

            default:
                return $files;
        }
    }

    /**
     * Checks if the remote file is empty or not.
     *
     * @param string $remoteFile The remote file path.
     *
     * @return bool Returns true if empty, otherwise returns false.
     *
     * @throws ClientException
     */
    public function isEmptyFile($remoteFile)
    {
        if ($this->isDir($remoteFile)) {
            throw new ClientException("[{$remoteFile}] is a directory.");
        }

        return ($this->fileSize($remoteFile) === 0);
    }

    /**
     * Gets a regular remote file size.
     *
     * @param string $remoteFile The remote file path.
     *
     * @return int Return the size in bytes.
     *
     * @throws ClientException
     */
    public function fileSize($remoteFile)
    {
        if ($this->isDir($remoteFile)) {
            throw new ClientException("[{$remoteFile}] must be an existing file.");
        }

        /**
         * SIZE command not a standard in the basic FTP protocol as defined in RFC 959,
         * so many FTP servers may not implement this command, to work around we use the
         * listDirectoryDetails() method which uses the ftp_rawlist FTP extension function,
         * in turn this function uses the LIST command to get the directory files
         * information includes the files size.
         *
         * @link https://tools.ietf.org/html/rfc959
         */

        if ($this->isFeatureSupported('SIZE')) {
            $list = $this->listDirectoryDetails('/');
            foreach (range(0, count($list) - 1) as $i) {
                if ($list[$i]['name'] === $remoteFile) {
                    return $list[$i]['size'];
                }
            }
        }

        return $this->wrapper->size($remoteFile);
    }

    /**
     * Moves file or a directory to another path.
     *
     * @param string $source      The remote file to be moved.
     * @param string $destination The destination remote directory.
     *
     * @return bool Returns true in success, an exception throws otherwise.
     *
     * @throws ClientException
     */
    public function move($source, $destination)
    {
        if (!$this->isExists($source)) {
            throw new ClientException("[{$source}] source file does not exists.");
        }

        if (!$this->isDir($destination)) {
            throw new ClientException("[{$destination}] must be an existing directory.");
        }

        return $this->rename($source, $destination . '/' . basename($source));
    }

    /**
     * Renames file/directory on the FTP server.
     *
     * @param string $remoteFile The remote file to renames.
     * @param string $newName    The new name.
     *
     * @return bool Returns true in success, otherwise an exception throws.
     *
     * @throws ClientException
     */
    public function rename($remoteFile, $newName)
    {
        if (!$this->isExists($remoteFile)) {
            throw new ClientException("[{$remoteFile}] doesn't exists.");
        }

        if ($this->isExists($newName)) {
            throw new ClientException("[{$newName}] is already exists.");
        }

        if (!$this->wrapper->rename($remoteFile, $newName)) {
            throw new ClientException(ClientException::getFtpServerError()
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
     * @throws ClientException
     */
    public function allocateSpace($bytes)
    {
        if (!is_int($bytes)) {
            throw new ClientException("[{$bytes}] must be of type integer.");
        }

        if (!$this->wrapper->alloc($bytes)) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Can't allocate [{$bytes}] bytes.");
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
     * @param int      $localFile  The local file path.
     * @param bool     $resume     [optional] resume downloading the file, the default is true.
     * @param int      $mode       [optional] The mode which will be used to transfer the file, the default is
     *                           the binary mode, if you don't know which mode you can use
     *                           {@link FtpClient::getTransferMode()}.
     *
     * @return bool Return true in success, otherwise an exception throws.
     *
     * @throws ClientException
     */
    public function download($remoteFile, $localFile, $resume = true, $mode = FtpWrapper::BINARY)
    {
        if (!$this->isExists($remoteFile)) {
            throw new ClientException("[{$remoteFile}] does not exists.");
        }

        $startPos = 0;
        if ($resume) {
            $startPos = filesize($localFile);
        }

        if (!$this->wrapper->get($localFile, $remoteFile, $mode, $startPos)) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Unable to retrieve the file [{$remoteFile}].");
        }

        return true;
    }

    /**
     * Gets appropriate transfer mode of the giving file.
     *
     * Note! this method gives you the transfer mode basing on the
     * giving file extension.
     *
     * @param $fileName
     *
     * @return int
     */
    public function getTransferMode($fileName)
    {
        if (
            in_array(substr($fileName, strpos($fileName, '.') + 1), [
                "3dm", "3ds", "3g2", "3gp", "7z", "a", "aac", "adp", "ai", "aif", "aiff", "alz", "apk", "ape", "ar",
                "arj", "asf", "au", "avi", "bak", "baml", "bh", "bin", "bk", "bmp", "btif", "bz2", "bzip2", "cab",
                "caf", "cgm", "class", "cmx", "cpio", "cr2", "cur", "dat", "dcm", "deb", "dex", "djvu", "dll", "dmg",
                "dng", "doc", "docm", "docx", "dot", "dotm", "dra", "DS_Store", "dsk", "dts", "dtshd", "dvb", "dwg",
                "dxf", "ecelp4800", "ecelp7470", "ecelp9600", "egg", "eol", "eot", "epub", "exe", "f4v", "fbs", "fh",
                "fla", "flac", "fli", "flv", "fpx", "fst", "fvt", "g3", "gh", "gif", "graffle", "gz", "gzip", "h261",
                "h263", "h264", "icns", "ico", "ief", "img", "ipa", "iso", "jar", "jpeg", "jpg", "jpgv", "jpm", "jxr",
                "key", "ktx", "lha", "lib", "lvp", "lz", "lzh", "lzma", "lzo", "m3u", "m4a", "m4v", "mar", "mdi", "mht",
                "mid", "midi", "mj2", "mka", "mkv", "mmr", "mng", "mobi", "mov", "movie", "mp3", "mp4", "mp4a", "mpeg",
                "mpg", "mpga", "mxu", "nef", "npx", "numbers", "nupkg", "o", "oga", "ogg", "ogv", "otf", "pages", "pbm",
                "pcx", "pdb", "pdf", "pea", "pgm", "pic", "png", "pnm", "pot", "potm", "potx", "ppa", "ppam", "ppm",
                "pps", "ppsm", "ppsx", "ppt", "pptm", "pptx", "psd", "pya", "pyc", "pyo", "pyv", "qt", "rar", "ras",
                "raw", "resources", "rgb", "rip", "rlc", "rmf", "rmvb", "rtf", "rz", "s3m", "s7z", "scpt", "sgi",
                "shar", "sil", "sketch", "slk", "smv", "snk", "so", "stl", "suo", "sub", "swf", "tar", "tbz", "tbz2",
                "tga", "tgz", "thmx", "tif", "tiff", "tlz", "ttc", "ttf", "txz", "udf", "uvh", "uvi", "uvm", "uvp",
                "uvs", "uvu", "viv", "vob", "war", "wav", "wax", "wbmp", "wdp", "weba", "webm", "webp", "whl", "wim",
                "wm", "wma", "wmv", "wmx", "woff", "woff2", "wrm", "wvx", "xbm", "xif", "xla", "xlam", "xls", "xlsb",
                "xlsm", "xlsx", "xlt", "xltm", "xltx", "xm", "xmind", "xpi", "xpm", "xwd", "xz", "z", "zip", "zipx"
            ])
        ) {
            return FtpWrapper::BINARY;
        }

        return FtpWrapper::ASCII;
    }

    /**
     * Retrieves a remote file asynchronously (non-blocking).
     *
     * @see FtpWrapper::nbGet()
     *
     * @param string $remoteFile         The remote file to download.
     * @param string $localFile          The local file path.
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
     * @throws ClientException
     */
    public function asyncDownload(
        $remoteFile,
        $localFile,
        $doWhileDownloading,
        $resume = true,
        $interval = 1,
        $mode = FtpWrapper::BINARY
    ) {
        if (!$this->isExists($remoteFile)) {
            throw new ClientException("[{$remoteFile}] does not exists.");
        }

        $startPos = 0;
        if ($resume) {
            if (file_exists($localFile)) {
                clearstatcache();
                $startPos = filesize($localFile);
            }
        }

        $remoteFileSize = $this->fileSize($remoteFile);

        $download = $this->wrapper->nbGet(
            $localFile,
            $remoteFile,
            $mode,
            $startPos
        );

        $startTime = microtime(true);

        $sizeTmp        = $startPos;
        $elapsedTimeTmp = 0;
        while ($download === FtpWrapper::MOREDATA) {
            $download = $this->wrapper->nbContinue();

            $elapsedTime = ceil(microtime(true) - $startTime);

            /**
             * The first condition : perform the callback function only once every interval time.
             * The second one      : perform the callback function every interval time.
             *
             * The integer cast inside the is_int is the second condition is because
             * the elapsedTime is a float number.
             *
             * A small simulation of the first 2 seconds supposing the interval is sets to 1 :
             *
             * Time(0.5s)  : (0 !== 1 && is_int( (int) 0.5f  / 1) => false
             * Time(1.01s) : (1 !== 2 && is_int( (int) 1.01f / 1) => true
             * Time(1.5s)  : (2 !== 2 && is_int( (int) 1.5f  / 1) => false
             * Time(2s)    : (2 !== 2 && is_int( (int) 2f    / 1) => false
             * Time(2.01s) : (2 !== 3 && is_int( (int) 2.01f / 1) => true
             */
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

        if ($download === FtpWrapper::FAILED) {
            throw new ClientException("Downloading the file [{$remoteFile}] was failed.");
        }

        return (bool)FtpWrapper::FINISHED;
    }

    /**
     * Reads the remote file content and returns the data as a string.
     *
     * @param string $remoteFile
     *
     * @return string
     *
     * @throws ClientException
     */
    public function getFileContent($remoteFile)
    {
        if ($this->isDir($remoteFile)) {
            throw new ClientException("[{$remoteFile}] is a directory.");
        }

        // Create a temporary file in the system temp files directory
        $tempFile = tempnam(sys_get_temp_dir(), $remoteFile);
        if (!$this->wrapper->get($tempFile, $remoteFile, FtpWrapper::ASCII)) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Unable to get [{$remoteFile}] content.");
        }

        $content = file_get_contents($tempFile);
        unlink($tempFile); // delete the temp file

        return $content;
    }

    /**
     * Creates a file on the FTP server and inserting the giving content to it.
     *
     * @param string   $fileName
     * @param mixed|null $content
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function createFile($fileName, $content = null)
    {
        if (!$this->setFileContent($fileName, (string)$content)) {
            throw new ClientException("Unable to create the file [{$fileName}].");
        }

        return true;
    }

    /**
     * Inserts giving contents to the specified remote file.
     *
     * Note! if the file does not exists it will be created.
     *
     * @param string $remoteFile The remote file path.
     * @param mixed    $content    The content to write in a remote file.
     *
     * @return bool Returns true in success, an exception throws in error.
     *
     * @throws ClientException
     */
    public function setFileContent($remoteFile, $content)
    {
        // Create file pointer to a temp file
        $handle = fopen('php://temp', 'a');
        fwrite($handle, (string)$content);
        rewind($handle); // Rewind position

        if (!$this->wrapper->fput($remoteFile, $handle, FtpWrapper::ASCII)) {
            throw new ClientException("Unable to set [{$remoteFile}] content.");
        }

        return true;
    }

    /**
     * Starts uploading the giving local file to the FTP server.
     *
     * @param string|int $localFile
     * @param            $remoteFile
     * @param bool         $resume
     * @param int          $mode
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function upload($localFile, $remoteFile, $resume = true, $mode = FtpWrapper::BINARY)
    {
        if (!file_exists($localFile)) {
            throw new ClientException("Cannot uploading the file [{$localFile}] because is not exists.");
        }

        $startPos = 0;
        if ($resume) {
            if ($this->isExists($remoteFile)) {
                $startPos = $this->fileSize($remoteFile);
            }
        }

        if (!$this->wrapper->put($remoteFile, $localFile, $mode, $startPos)) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Unable to upload the file [{$localFile}].");
        }

        return true;
    }

    /**
     * Uploading a local file asynchronously to the remote server.
     *
     * @param string $localFile          The local file to upload.
     * @param string $remoteFile         The remote file path.
     * @param string $doWhileDownloading A callback function performed asynchronously while downloading the remote
     *                                     file.
     * @param bool     $resume             [optional] resume downloading the file, the default is true.
     * @param int      $interval           [optional] An optional parameter represent the interval in seconds that the
     *                                     callback function will repeatedly called every specific interval until the
     *                                     transfer is complete, the default value sets to 1 seconds.
     * @param int      $mode               [optional] The mode which will be used to transfer the file, the default is
     *                                     the binary mode, if you don't know which mode you can use
     *                                     {@link FtpClient::getTransferMode()}.
     *
     * @return bool Return true if file successfully uploaded, if not an exception throws.
     *
     * @throws ClientException
     */
    public function asyncUpload(
        $localFile,
        $remoteFile,
        $doWhileDownloading,
        $resume = true,
        $interval = 1,
        $mode = FtpWrapper::BINARY
    ) {
        if (!file_exists($localFile)) {
            throw new ClientException("[{$localFile}] doesn't exists to upload.");
        }

        $startPos = 0;
        if ($resume) {
            if ($this->isExists($remoteFile)) {
                $startPos = $this->fileSize($remoteFile);
            }
        }

        $localFileSize = filesize($localFile);
        $handle        = fopen($localFile, 'r');

        /**
         * To check asynchronously the uploading state we use the ftp_nb_fput function instead
         * of ftp_nb_put, by passing the local file pointer to this function we will
         * be able to know the remote file size every time using the ftell function.
         */
        $download = $this->wrapper->nbFput(
            $remoteFile,
            $handle,
            $mode,
            $startPos
        );

        $startTime = microtime(true);

        $sizeTmp        = null;
        $elapsedTimeTmp = null;
        while ($download === FtpWrapper::MOREDATA) {
            $download = $this->wrapper->nbContinue();

            $elapsedTime = ceil(microtime(true) - $startTime);

            /**
             * The first condition : perform the callback function only once every interval time.
             * The second one      : perform the callback function every interval time.
             *
             * The integer cast inside the is_int is the second condition is because
             * the elapsedTime is a float number.
             *
             * A small simulation of the first 2 seconds supposing the interval is sets to 1 :
             *
             * Time(0.5s)  : (0 !== 1 && is_int( (int) 0.5f  / 1) => false
             * Time(1.01s) : (1 !== 2 && is_int( (int) 1.01f / 1) => true
             * Time(1.5s)  : (2 !== 2 && is_int( (int) 1.5f  / 1) => false
             * Time(2s)    : (2 !== 2 && is_int( (int) 2f    / 1) => false
             * Time(2.01s) : (2 !== 3 && is_int( (int) 2.01f / 1) => true
             */
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

        if ($download === FtpWrapper::FAILED) {
            throw new ClientException("Failed to upload the file [{$localFile}].");
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
     * @throws ClientException
     */
    public function setPermissions($filename, $mode)
    {
        if (is_array($mode)) {
            foreach ($mode as $key => $value) {
                if (!in_array($key, ['owner', 'group', 'other'])) {
                    throw new ClientException("[{$key}] is invalid permission group.");
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
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Failed to set permissions to [{$filename}]");
        }

        return true;
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
