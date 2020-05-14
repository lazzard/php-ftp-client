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
     * Gets parent of the current directory.
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
     * @return string
     */
    public function getCurrentDir()
    {
        return $this->wrapper->pwd();
    }

    /**
     * @param string $directory
     *
     * @throws ClientException
     */
    public function setCurrentDir($directory)
    {
        if ( ! $this->isDir($directory)) {
            throw new ClientException("[{$directory}] is not a directory.");
        }

        if ( ! $this->wrapper->chdir($directory)) {
            throw new ClientException(
                ClientException::getFtpServerError()
                    ?: "Unable to change the current directory to [{$directory}]."
            );
        }
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
        if ( ! $this->wrapper->cdup()) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Unable to change to the parent directory."
            );
        }

        return true;
    }

    /**
     * Get files count of the giving directory.
     *
     * @see FtpClient::listDirectoryDetails()
     *
     * @param bool   $recursive  [optional]
     * @param int    $filter     [optional]
     * @param bool   $ignoreDots [optional]
     *
     * @param string $directory
     *
     * @return int
     *
     * @throws ClientException
     */
    public function getCount(
        $directory, $recursive = false, $filter = self::FILE_DIR_TYPE, $ignoreDots = false
    )
    {
        return count($this->listDirectoryDetails(
            $directory,
            $recursive,
            $filter,
            $ignoreDots
        ));
    }

    /**
     * Get detailed list of files in the giving directory.
     *
     * @param string $directory
     * @param bool   $recursive  [optional]
     * @param int    $filter     [optional]
     * @param bool   $ignoreDots [optional]
     *
     * @return array
     *
     * @throws ClientException
     */
    public function listDirectoryDetails($directory, $recursive = false, $filter = self::FILE_DIR_TYPE, $ignoreDots = true)
    {
        if ( ! $this->isDir($directory)) {
            throw new ClientException("[{$directory}] is not a directory.");
        }

        if ( ! ($details = $this->wrapper->rawlist($directory, $recursive))) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Unable to get files list for [{$directory}] directory."
            );
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
                    if ($type === 'file' || $type === 'link') {
                        continue;
                    }
                }

                if ($ignoreDots) {
                    if (in_array($chunks[8], ['.', '..'])) {
                        continue;
                    }
                }

                if ( ! $pathTmp) {
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
     * Check whether if a file is a directory or not.
     *
     * @param string $directory
     *
     * @return bool Return true if the giving file is a directory,
     *              false if it's a file or the dir doesn't exists.
     */
    public function isDir($directory)
    {
        return ($this->wrapper->size($directory) === -1);
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
        if ( ! ($sysType = $this->wrapper->systype())) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Unable to get FTP server operating system type."
            );
        }

        return $sysType;
    }

    /**
     * Gets the default transfer type.
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

        if ( ! $response['success']) {
            throw new ClientException($response['message']);
        }

        return explode(' ', $response['message'], 3)[2];
    }

    /**
     * Get supported SITE commands by the remote server.
     *
     * @see FtpCommand::raw()
     *
     * @return array Return array of SITE available commands in success.
     *
     * @throws ClientException
     */
    public function getSupportedSiteCommands()
    {
        $response = $this->command->raw("SITE HELP");

        if ( ! $response['success']) {
            throw new ClientException($response['message']);
        }

        return array_map('ltrim', $response['body']);
    }

    /**
     * Delete an FTP file.
     *
     * @param string $remoteFile
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function removeFile($remoteFile)
    {
        if ( ! $this->isExists($remoteFile) || $this->isDir($remoteFile)) {
            throw new ClientException("[{$remoteFile}] must be an existing file.");
        }

        if ( ! $this->wrapper->delete($remoteFile)) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Unable to delete the file [{$remoteFile}]."
            );
        }

        return true;
    }

    /**
     * Check whether if the giving file/directory is exists or not.
     *
     * @param string $remoteFile
     *
     * @return bool
     */
    public function isExists($remoteFile)
    {
        return in_array(
            basename($remoteFile),
            $this->wrapper->nlist(dirname($remoteFile))
        );
    }

    /**
     * Delete an FTP remote directory.
     *
     * Be careful with this method, it well remove everything within the giving directory.
     *
     * @param string $directory
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function removeDirectory($directory)
    {
        if ( ! $this->isDir($directory)) {
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
     * Create an FTP directory.
     *
     * @param string $directory
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

            if ( ! $this->isDir($dir)) {
                $this->wrapper->mkdir($dir);
            }
        }

        return true;
    }

    /**
     * Gets last modified time for an FTP remote file.
     *
     * @param string      $remoteFile
     * @param string|null $format [optional]
     *
     * @return string|int Returns the string format if the format parameter was
     *                    specified, if not returns an numeric timestamp representation.
     *
     * @throws ClientException
     */
    public function lastMTime($remoteFile, $format = null)
    {
        /**
         * MDTM command not a standard in the basic FTP protocol as defined in RFC 959.
         */
        if ( ! $this->isFeatureSupported('MDTM')) {
            throw new ClientException("This feature not supported by the remote server.");
        }

        if ($this->isDir($remoteFile)) {
            throw new ClientException("[$remoteFile] is not a directory.");
        }

        if ( ! ($time = $this->wrapper->mdtm($remoteFile))) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Could not get last modified time for [{$remoteFile}]."
            );
        }

        return $format ? date($format, $time) : $time;
    }

    /**
     * Determine if the giving feature is supported by the remote server or not.
     *
     * Note! the characters case not important.
     *
     * @see FtpClient::getFeatures()
     *
     * @param string $feature
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function isFeatureSupported($feature)
    {
        return in_array(
            strtolower($feature),
            array_map('strtolower', $this->getFeatures())
        );
    }

    /**
     * Get supported the additional commands outside the basic commands
     * defined in RFC959.
     *
     * @see FtpCommand::raw()
     *
     * @return array
     *
     * @throws ClientException
     */
    public function getFeatures()
    {
        $response = $this->command->raw("FEAT");

        if ( ! $response['success']) {
            throw new ClientException($response['message']);
        }

        return array_map('ltrim', $response['body']);
    }

    /**
     * Gets directory size.
     *
     * @param string $directory
     *
     * @return int Return the size on bytes.
     *
     * @throws ClientException
     */
    public function dirSize($directory)
    {
        if ( ! $this->isDir($directory)) {
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
     * Check whether if the giving directory is empty or not.
     *
     * @param string $directory
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function isEmptyDir($directory)
    {
        if ( ! $this->isDir($directory)) {
            throw new ClientException("[{$directory}] is not directory.");
        }

        return empty($this->listDirectory($directory, true));
    }

    /**
     * Get list of files names in giving directory.
     *
     * @param string $directory
     * @param int    $filter
     * @param bool   $ignoreDots [optional] Ignore dots files items '.' and '..',
     *                           default sets to false.
     *
     * @return array
     *
     * @throws ClientException
     */
    public function listDirectory($directory, $filter = self::FILE_DIR_TYPE, $ignoreDots = true)
    {
        if ( ! $files = $this->wrapper->nlist($directory)) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Failed to get files list."
            );
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
                    return ! $this->isDir($file);
                });

            default:
                return $files;
        }
    }

    /**
     * Checks if the remote file is empty or not.
     *
     * @param $remoteFile
     *
     * @return bool
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
     * Gets file size.
     *
     * @param string $remoteFile
     *
     * @return int Return the size on bytes.
     *
     * @throws ClientException
     */
    public function fileSize($remoteFile)
    {
        if ($this->isDir($remoteFile)) {
            throw new ClientException("[{$remoteFile}] must be an existing file.");
        }

        /**
         * SIZE command not a standard in the basic FTP protocol as defined in RFC 959.
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
     * Move a file or a directory to another path.
     *
     * @param string $source      Source file
     * @param string $destination Destination directory
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function move($source, $destination)
    {
        if ( ! $this->isExists($source)) {
            throw new ClientException("[{$source}] source file does not exists.");
        }

        if ( ! $this->isDir($destination)) {
            throw new ClientException("[{$destination}] must be an existing directory.");
        }

        return $this->rename($source, $destination . '/' . basename($source));
    }

    /**
     * Rename a file/directory.
     *
     * @param string $oldName
     * @param string $newName
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function rename($oldName, $newName)
    {
        if ( ! $this->isExists($oldName)) {
            throw new ClientException("[{$oldName}] doesn't exists.");
        }

        if ($this->isExists($newName)) {
            throw new ClientException("[{$newName}] is already exists.");
        }

        if ( ! $this->wrapper->rename($oldName, $newName)) {
            throw new ClientException(
                ClientException::getFtpServerError()
                    ?: sprintf(
                    "Unable to rename %s to %s",
                    $oldName,
                    $newName
                ));
        }

        return true;
    }

    /**
     * Check if the FTP server is still connected and responds for commands.
     *
     * @see FtpCommand::raw()
     *
     * @return bool
     */
    public function isServerAlive()
    {
        return $this->command->raw("NOOP")['success'];
    }

    /**
     * Send a request to FTP server to allocate a space for the next file transfer.
     *
     * @param int
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function allocateSpace($bytes)
    {
        if ( ! is_int($bytes)) {
            throw new ClientException("[{$bytes}] must be of type integer.");
        }

        // TODO ftp_alloc warning issue
        if ( ! $this->wrapper->alloc($bytes)) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Can't allocate [{$bytes}] bytes."
            );
        }

        return true;
    }

    /**
     * Download remote file from the FTP server.
     *
     * @param string $remoteFile
     * @param int    $saveAs  [optional]
     * @param int    $retries [optional]
     * @param int    $mode    [optional]
     * @param int    $startAt [optional]
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function download($remoteFile, $saveAs, $retries = 1, $mode = null, $startAt = 0)
    {
        if ( ! $this->isExists($remoteFile)) {
            throw new ClientException("[{$remoteFile}] does not exists.");
        }

        $i = 0;
        do {
            if ( ! $this->wrapper->get(
                $saveAs,
                $remoteFile,
                $mode,
                $startAt
            )
            ) {
                $i++;
                if ($i >= $retries) {
                    throw new ClientException(ClientException::getFtpServerError()
                        ?: "Unable to retrieve [{$remoteFile}]."
                    );
                }
            } else {
                break;
            }
        } while ($i < $retries);

        return true;
    }

    /**
     * Resume downloading broken file.
     *
     * @param string $localFile
     * @param string $remoteFile
     * @param int    $retries [optional]
     * @param int    $mode    [optional]
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function resumeDownload($localFile, $remoteFile, $retries = 1, $mode = null)
    {
        if ( ! file_exists($localFile)) {
            throw new ClientException("Cannot resume downloading [{$localFile}] because is doesn't exists.");
        }

        return $this->download(
            $remoteFile,
            $localFile,
            $retries,
            $mode,
            filesize($localFile)
        );
    }

    /**
     * Gets the appropriate transfer mode for the giving file.
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
        if (in_array(substr($fileName, strpos($fileName, '.') + 1), [
            "3dm", "3ds", "3g2", "3gp", "7z", "a", "aac", "adp", "ai", "aif", "aiff", "alz", "ape",
            "apk", "ar", "arj", "asf", "au", "avi", "bak", "baml", "bh", "bin", "bk", "bmp", "btif",
            "bz2", "bzip2", "cab", "caf", "cgm", "class", "cmx", "cpio", "cr2", "cur", "dat", "dcm",
            "deb", "dex", "djvu", "dll", "dmg", "dng", "doc", "docm", "docx", "dot", "dotm", "dra",
            "DS_Store", "dsk", "dts", "dtshd", "dvb", "dwg", "dxf", "ecelp4800", "ecelp7470",
            "ecelp9600", "egg", "eol", "eot", "epub", "exe", "f4v", "fbs", "fh", "fla", "flac",
            "fli", "flv", "fpx", "fst", "fvt", "g3", "gh", "gif", "graffle", "gz", "gzip", "h261",
            "h263", "h264", "icns", "ico", "ief", "img", "ipa", "iso", "jar", "jpeg", "jpg", "jpgv",
            "jpm", "jxr", "key", "ktx", "lha", "lib", "lvp", "lz", "lzh", "lzma", "lzo", "m3u",
            "m4a", "m4v", "mar", "mdi", "mht", "mid", "midi", "mj2", "mka", "mkv", "mmr", "mng",
            "mobi", "mov", "movie", "mp3", "mp4", "mp4a", "mpeg", "mpg", "mpga", "mxu", "nef",
            "npx", "numbers", "nupkg", "o", "oga", "ogg", "ogv", "otf", "pages", "pbm", "pcx",
            "pdb", "pdf", "pea", "pgm", "pic", "png", "pnm", "pot", "potm", "potx", "ppa", "ppam",
            "ppm", "pps", "ppsm", "ppsx", "ppt", "pptm", "pptx", "psd", "pya", "pyc", "pyo", "pyv",
            "qt", "rar", "ras", "raw", "resources", "rgb", "rip", "rlc", "rmf", "rmvb", "rtf", "rz",
            "s3m", "s7z", "scpt", "sgi", "shar", "sil", "sketch", "slk", "smv", "snk", "so", "stl",
            "suo", "sub", "swf", "tar", "tbz", "tbz2", "tga", "tgz", "thmx", "tif", "tiff", "tlz",
            "ttc", "ttf", "txz", "udf", "uvh", "uvi", "uvm", "uvp", "uvs", "uvu", "viv", "vob",
            "war", "wav", "wax", "wbmp", "wdp", "weba", "webm", "webp", "whl", "wim", "wm", "wma",
            "wmv", "wmx", "woff", "woff2", "wrm", "wvx", "xbm", "xif", "xla", "xlam", "xls", "xlsb",
            "xlsm", "xlsx", "xlt", "xltm", "xltx", "xm", "xmind", "xpi", "xpm", "xwd", "xz", "z",
            "zip", "zipx"
        ])
        ) {
            return FtpWrapper::BINARY;
        }

        return FtpWrapper::ASCII;
    }

    /**
     * Retrieves a remote file asynchronously (non-blocking).
     *
     * @param string   $remoteFile
     * @param string   $saveAs
     * @param callback $doWhileDownloading
     * @param int      $interval [optional]
     * @param int      $mode     [optional]
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function asyncDownload($remoteFile, $saveAs, $doWhileDownloading, $interval = 1, $mode = null)
    {
        if ( ! $this->isExists($remoteFile)) {
            throw new ClientException("[{$remoteFile}] does not exists.");
        }

        $remoteFileSize = $this->fileSize($remoteFile);

        $download = $this->wrapper->nb_get(
            $saveAs,
            $remoteFile,
            $mode,
            0
        );

        $timeStart = microtime(true);

        $sizeTmp        = null;
        $elapsedTimeTmp = null;
        while ($download === FtpWrapper::MOREDATA) {
            $download = $this->wrapper->nb_continue();

            $elapsedTime = ceil(microtime(true) - $timeStart);

            if ($elapsedTimeTmp !== $elapsedTime && is_int(intval($elapsedTime) / $interval)) {
                clearstatcache();
                $localFileSizeSync = filesize($saveAs);

                $doWhileDownloading($this->getAsyncStatInfo([
                    'sizeSync'         => $localFileSizeSync,
                    'previousSyncSize' => $sizeTmp,
                    'sourceSize'       => $remoteFileSize,
                    'elapsedTime'      => $elapsedTime
                ]));

                $sizeTmp = $localFileSizeSync;
            }

            $elapsedTimeTmp = $elapsedTime;
        }

        if ($download === FtpWrapper::FAILED) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Failed to download [{$remoteFile}]."
            );
        }

        return (bool)FtpWrapper::FINISHED;
    }

    /**
     * Resume downloading file asynchronously.
     *
     * Note : the autoSeek option must be turned ON (default), otherwise
     * the download will start from the beginning.
     *
     * @param string $remoteFile
     * @param string $localFile
     * @param string $doWhileDownloading
     * @param int    $interval [optional]
     * @param int    $mode     [optional]
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function resumeAsyncDownload($remoteFile, $localFile, $doWhileDownloading, $interval = 1, $mode = null)
    {
        if ( ! file_exists($localFile)) {
            throw new ClientException("[{$localFile}] must be an existing file.");
        }

        if ( ! $this->isExists($remoteFile)) {
            throw new ClientException("[{$remoteFile}] does not exists.");
        }

        $originSize     = filesize($localFile);
        $remoteFileSize = $this->fileSize($remoteFile);

        $download = $this->wrapper->nb_get(
            $localFile,
            $remoteFile,
            $mode,
            $originSize
        );

        $timeStart = microtime(true);

        $sizeTmp        = null;
        $elapsedTimeTmp = null;
        while ($download === FtpWrapper::MOREDATA) {
            $download = $this->wrapper->nb_continue();

            $elapsedTime = ceil(microtime(true) - $timeStart);

            if ($elapsedTimeTmp !== $elapsedTime && is_int(intval($elapsedTime) / $interval)) {
                clearstatcache();
                $localFileSize = filesize($localFile) - $originSize;

                $doWhileDownloading($this->getAsyncStatInfo([
                    'sizeSync'         => $localFileSize,
                    'previousSyncSize' => $sizeTmp,
                    'sourceSize'       => $remoteFileSize,
                    'originSize'       => $originSize,
                    'elapsedTime'      => $elapsedTime
                ], true));

                $sizeTmp = $localFileSize;
            }

            $elapsedTimeTmp = $elapsedTime;
        }

        if ($download === FtpWrapper::FAILED) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Failed to download the file [{$remoteFile}]."
            );
        }

        return (bool)FtpWrapper::FINISHED;
    }

    /**
     * Read the remote file content and return the data as a string.
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

        $tempFile = tempnam(sys_get_temp_dir(), $remoteFile);

        if ( ! $this->wrapper->get($tempFile, $remoteFile, FtpWrapper::ASCII)) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Unable to get [{$remoteFile}] content."
            );
        }

        $content = file_get_contents($tempFile);
        unlink($tempFile);

        return $content;
    }

    /**
     * Starts uploading the giving local file to the FTP server.
     *
     * @param string     $localFile
     * @param string|int $saveAs
     * @param int        $retries
     * @param int        $mode
     * @param int        $startAt
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function upload($localFile, $saveAs, $retries = 1, $mode = null, $startAt = 0)
    {
        if ( ! file_exists($localFile)) {
            throw new ClientException("[{$localFile}] does not exists.");
        }

        $i = 0;
        do {
            if ( ! $this->wrapper->put(
                $saveAs,
                $localFile,
                $mode,
                $startAt)
            ) {
                $i++;
                if ($i >= $retries) {
                    throw new ClientException(ClientException::getFtpServerError()
                        ?: "Unable to upload [{$localFile}]."
                    );
                }
            } else {
                break;
            }
        } while ($i < $retries);

        return true;
    }

    /**
     * Inserts the giving contents to a specific FTP remote file.
     *
     * Note : if the file does not exists it will be created.
     *
     * @param string $remoteFile
     * @param mixed  $content
     *
     * @return bool
     */
    public function setFileContent($remoteFile, $content)
    {
        $handle = fopen('php://temp', 'a');
        fwrite($handle, (string)$content);
        rewind($handle);

        return $this->wrapper->fput($remoteFile, $handle, FtpWrapper::ASCII);
    }

    /**
     * Create a file on the FTP server and inserting the giving content to it.
     *
     * @param string     $fileName
     * @param mixed|null $content
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function createFile($fileName, $content = null)
    {
        if ( ! $this->setFileContent($fileName, (string)$content)) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Unable to create the file [{$fileName}]."
            );
        }

        return true;
    }

    /**
     * Resume uploading a local file.
     *
     * @param string $localFile
     * @param string $remoteFile
     * @param int    $retries [optional]
     * @param int    $mode    [optional]
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function resumeUpload($localFile, $remoteFile, $retries = 1, $mode = null)
    {
        if ( ! $this->isExists($remoteFile)) {
            throw new ClientException(
                "Cannot resume uploading [{$localFile}] because is doesn't exists."
            );
        }

        return $this->upload(
            $localFile,
            $remoteFile,
            $retries,
            $mode,
            $this->fileSize($remoteFile)
        );
    }

    /**
     * Uploading a local file asynchronously.
     *
     * @param string $localFile
     * @param string $saveAs
     * @param string $doWhileDownloading
     * @param int    $interval [optional]
     * @param int    $mode     [optional]
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function asyncUpload($localFile, $saveAs, $doWhileDownloading, $interval = 1, $mode = null)
    {
        if ( ! file_exists($localFile)) {
            throw new ClientException("[{$localFile}] does not exists.");
        }

        $localFileSize = filesize($localFile);
        $handle        = fopen($localFile, 'r');

        /**
         * To check asynchronously the uploading state we use the ftp_nb_fput
         * function instead of ftp_nb_put, by passing the local file pointer to this function we will
         * be able to know the local file file pointer position any time we want
         * using the ftell function.
         */

        $download = $this->wrapper->nb_fput(
            $saveAs,
            $handle,
            $mode,
            0
        );

        $timeStart = microtime(true);

        $sizeTmp        = null;
        $elapsedTimeTmp = null;
        while ($download === FtpWrapper::MOREDATA) {
            $download = $this->wrapper->nb_continue();

            $elapsedTime = ceil(microtime(true) - $timeStart);

            if ($elapsedTimeTmp !== $elapsedTime && is_int(intval($elapsedTime) / $interval)) {

                $localFileSizeSync = ftell($handle);

                $doWhileDownloading($this->getAsyncStatInfo([
                    'sizeSync'         => $localFileSizeSync,
                    'previousSyncSize' => $sizeTmp,
                    'sourceSize'       => $localFileSize,
                    'elapsedTime'      => $elapsedTime
                ]));

                $sizeTmp = $localFileSizeSync;
            }

            $elapsedTimeTmp = $elapsedTime;
        }

        if ($download === FtpWrapper::FAILED) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Failed to upload the file [{$localFile}]."
            );
        }

        return (bool)FtpWrapper::FINISHED;
    }

    /**
     * Continues uploading a remote file asynchronously.
     *
     * @param string $localFile
     * @param string $remoteFile
     * @param string $doWhileDownloading
     * @param int    $interval [optional]
     * @param int    $mode     [optional]
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function resumeAsyncUpload($localFile, $remoteFile, $doWhileDownloading, $interval = 1, $mode)
    {
        if ( ! file_exists($localFile)) {
            throw new ClientException("[{$localFile}] does not exists.");
        }

        $localFileSize = filesize($localFile);
        $originSize    = $this->fileSize($remoteFile);

        $handle = fopen($localFile, 'r');

        /**
         * To check asynchronously the uploading state we use the ftp_nb_fput
         * function instead of ftp_nb_put, by passing the local file pointer to this function we will
         * be able to know the local file file pointer position any time we want
         * using the ftell function.
         */

        $download = $this->wrapper->nb_fput(
            $remoteFile,
            $handle,
            $mode,
            $originSize
        );

        $timeStart = microtime(true);

        $sizeTmp        = null;
        $elapsedTimeTmp = null;
        while ($download === FtpWrapper::MOREDATA) {
            $download = $this->wrapper->nb_continue();

            $elapsedTime = ceil(microtime(true) - $timeStart);

            if ($elapsedTimeTmp !== $elapsedTime && is_int(intval($elapsedTime) / $interval)) {

                $sizeSync = ftell($handle) - $originSize;

                $doWhileDownloading($this->getAsyncStatInfo([
                    'sizeSync'         => $sizeSync,
                    'previousSyncSize' => $sizeTmp,
                    'sourceSize'       => $localFileSize,
                    'originSize'       => $originSize,
                    'elapsedTime'      => $elapsedTime,
                ], true));

                $sizeTmp = $sizeSync;
            }

            $elapsedTimeTmp = $elapsedTime;
        }

        if ($download === FtpWrapper::FAILED) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Failed to upload the file [{$localFile}]."
            );
        }

        return (bool)FtpWrapper::FINISHED;
    }

    /**
     * Sets permissions on an FTP file or directory.
     *
     * @param string           $filename
     * @param array|int|string $mode
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function setPermissions($filename, $mode)
    {
        if (is_array($mode)) {
            foreach ($mode as $key => $value) {
                if ( ! in_array($key, ['owner', 'group', 'other'])) {
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

        if ( ! $this->wrapper->chmod($mode, $filename)) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Failed to set permissions to [{$filename}]"
            );
        }

        return true;
    }

    /**
     * @param string $chmod
     *
     * @return int|mixed
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

    /**
     * Gets miscellaneous information of the giving upload/download operation state.
     *
     * @param array $state
     * @param bool  $resume
     *
     * @return array
     */
    protected function getAsyncStatInfo($state, $resume = false)
    {
        if ( ! $resume) {
            $percentage = number_format(
                ($state['sizeSync'] * 100) / $state['sourceSize']
            );
        } else {
            $percentage = number_format(
                ($state['sizeSync'] * 100) / ($state['sourceSize'] - $state['originSize'])
            );
        }

        return [
            'transferred' => number_format(
                ($state['sizeSync'] - $state['previousSyncSize']) / 1000
            ),
            'speed'       => number_format(
                ($state['sizeSync'] / $state['elapsedTime']) / 1000, 2
            ),
            'percentage'  => $percentage,
            'seconds'     => $state['elapsedTime']
        ];
    }

    /**
     * Extract the file type (type, dir, link) from chmod string
     * (e.g., 'drwxr - xr - x' will return 'dir').
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
}