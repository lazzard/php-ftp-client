<?php

namespace Lazzard\FtpClient;

use Lazzard\FtpClient\Command\FtpCommand;
use Lazzard\FtpClient\Connection\ConnectionInterface;
use Lazzard\FtpClient\Exception\ClientException;

/**
 * Class FtpClient
 *
 * @since   1.0
 * @package Lazzard\FtpClient
 * @author  EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class FtpClient
{
    /**
     * FtpClient predefined constants
     */
    const FILE_DIR_TYPE     = 0;
    const FILE_TYPE         = 2;
    const DIR_TYPE          = 1;
    const GET_TRANSFER_MODE = 3;
    const SAVE_CURRENT_DIR  = 4;

    /**
     * FtpWrapper constants
     */
    const ASCII    = FtpWrapper::ASCII;
    const BINARY   = FtpWrapper::BINARY;
    const FAILED   = FtpWrapper::FAILED;
    const FINISHED = FtpWrapper::FINISHED;
    const MOREDATA = FtpWrapper::MOREDATA;

    /** @var ConnectionInterface */
    protected $connection;

    /** @var FtpCommand */
    protected $command;

    /** @var FtpWrapper */
    protected $wrapper;

    /** @var string */
    protected $currentDir;

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
     * @param ConnectionInterface $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
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

        $this->currentDir = $directory;
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
     * @param string $directory
     * @param bool   $recursive  [optional]
     * @param int    $filter
     * @param bool   $ignoreDots [optional]
     *
     * @return int
     *
     * @see FtpClient::listDirectoryDetails()
     *
     * @throws ClientException
     */
    public function getCount(
        $directory, $recursive = false, $filter = self::FILE_DIR_TYPE,
        $ignoreDots = false
    ) {
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
     * @param int    $filter
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
     * Check weather if a file is a directory or not.
     *
     * @param string $directory
     *
     * @return bool Return true if the giving file is a directory,
     *              false if isn't or the file doesn't exists.
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
     * @see FtpCommand::rawRequest()
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
     * Gets the default transfer type on the FTP server.
     *
     * @return string
     *
     * @see FtpCommand::rawRequest()
     *
     * @throws ClientException
     */
    public function getDefaultTransferType()
    {
        if ( ! $this->command->rawRequest("SYST")->isSucceeded()) {
            throw new ClientException($this->command->getResponseMessage());
        }

        return explode(' ', $this->command->getResponseMessage(), 2)[1];
    }

    /**
     * Get supported SITE commands by the remote server.
     *
     * @return array Return array of SITE available commands in success.
     *
     * @see FtpCommand::rawRequest()
     *
     * @throws ClientException
     */
    public function getSupportedSiteCommands()
    {
        if ( ! $this->command->rawRequest("HELP")->isSucceeded()) {
            throw new ClientException($this->command->getResponseMessage());
        }

        return array_map('ltrim', $this->command->getResponseBody());
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
     * Check weather if the giving file/directory is exists or not.
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
        // TODO replace size
        if ($this->wrapper->size($directory) !== -1) {
            throw new ClientException("[{$directory}] must be an existing directory.");
        }

        if ( ! ($list = $this->wrapper->nlist($directory))) {
            $this->removeDirectory($directory);
        }

        if ( ! empty($list)) {
            foreach ($list as $file) {
                $path = "$directory/$file";

                if (in_array(basename($path), ['.', '..'])) {
                    continue;
                }

                // TODO replace size
                if ($this->wrapper->size($path) !== -1) {
                    $this->wrapper->delete($path);
                } elseif ($this->wrapper->rmdir($path) !== true) {
                    $this->removeDirectory($path);
                }
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
        // TODO consider to remove this check
        if ( ! $this->isFeatureSupported('MDTM')) {
            throw new ClientException("This feature not supported by the remote server.");
        }

        // TODO implementation for directories
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
     * Note : the characters case not important.
     *
     * @param string $feature
     *
     * @return bool
     *
     * @see FtpClient::getFeatures()
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
     * Get supported arbitrary command on the FTP server.
     *
     * @return array
     *
     * @see FtpCommand::rawRequest()
     *
     * @throws ClientException
     */
    public function getFeatures()
    {
        if ( ! $this->command->rawRequest("FEAT")->isSucceeded()) {
            throw new ClientException($this->command->getResponseMessage());
        }

        return array_map('ltrim', $this->command->getResponseBody());
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
     * Check weather if the giving directory is empty or not.
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
     * @param bool   $ignoreDots              [optional] Ignore dots files items '.' and '..',
     *                                        default sets to false.
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
     * @return bool
     */
    public function isServerAlive()
    {
        return $this->command->rawRequest("NOOP")->isSucceeded();
    }

    /**
     * Send a request to allocate a space.
     *
     * @param int
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function allocateSpace($bytes)
    {
        if ( ! is_double($bytes)) {
            throw new ClientException("[{$bytes}] must be of type integer.");
        }

        // TODO ftp_alloc warning problem
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
     * @param int    $saveIn [optional]
     * @param int    $mode   [optional]
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function download($remoteFile, $saveIn = self::SAVE_CURRENT_DIR, $mode = self::GET_TRANSFER_MODE)
    {
        if ( ! $this->isExists($remoteFile)) {
            throw new ClientException("[{$remoteFile}] does not exists.");
        }

        $saveIn = $saveIn === self::SAVE_CURRENT_DIR ? getcwd() : $saveIn;

        if ( ! $this->wrapper->get(
            $saveIn . DIRECTORY_SEPARATOR . basename($remoteFile),
            $remoteFile,
            $mode === self::GET_TRANSFER_MODE ? $this->getTransferMode($remoteFile) : $mode)
        ) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Unable to retrieve [{$remoteFile}]."
            );
        }

        return true;
    }

    /**
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
            return self::BINARY;
        }

        return self::ASCII;
    }

    /**
     * Retrieves a remote file asynchronously (non-blocking).
     *
     * @param string        $remoteFile
     * @param callback|null $doWhileDownloading
     * @param int           $saveIn   [optional]
     * @param int           $interval [optional]
     * @param int           $mode     [optional]
     * @param int           $startDownloadAt
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function asyncDownload($remoteFile, $doWhileDownloading, $saveIn = self::SAVE_CURRENT_DIR, $interval = 1, $mode = self::GET_TRANSFER_MODE, $startDownloadAt = 0
    ) {
        if ( ! $this->isExists($remoteFile)) {
            throw new ClientException("[{$remoteFile}] does not exists.");
        }

        $saveIn    = $saveIn === self::SAVE_CURRENT_DIR ? getcwd() : $saveIn;
        $localFile = $saveIn . DIRECTORY_SEPARATOR . basename($remoteFile);

        $remoteFileSize = $this->wrapper->size($remoteFile);

        $download = $this->wrapper->nb_get(
            $localFile,
            $remoteFile,
            $mode === self::GET_TRANSFER_MODE ? $this->getTransferMode($remoteFile) : $mode,
            $startDownloadAt
        );

        $timeStart = microtime(true);

        $secondsTmp = null;
        $sizeTmp    = null;
        while ($download === self::MOREDATA) {
            $download = $this->wrapper->nb_continue();

            $secondsPassed = round(microtime(true) - $timeStart);

            if ($secondsPassed !== $secondsTmp && is_int(intval($secondsPassed) / $interval)) {
                clearstatcache();
                $localFileSize = filesize($localFile);

                $transferred = number_format(($localFileSize - $sizeTmp) / 1000);

                $speedAverage = ! $secondsPassed
                    ?: number_format(($localFileSize / $secondsPassed)
                        / 1000, 2); // second 0 => division by 0

                $progress = number_format(($localFileSize * 100) / $remoteFileSize, 0);

                ob_end_clean();
                ob_start();

                $doWhileDownloading($progress, $speedAverage, $transferred, $secondsPassed);

                ob_flush();
                flush();

                $sizeTmp = $localFileSize;
            }

            $secondsTmp = $secondsPassed;
        }

        if ($download === self::FAILED) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Failed to download the file [{$remoteFile}]."
            );
        }

        return $download === self::FINISHED;
    }

    /**
     * Resume downloading file asynchronously.
     *
     * Note : the autoSeek option must be turned ON (default), if not
     * the download will start at offset 0.
     *
     * @param string $localFile
     * @param string $remoteFile
     * @param string $doWhileDownloading
     * @param int    $interval
     * @param int    $mode
     *
     * @return bool
     *
     * @throws ClientException
     */
    public function resumeAsyncDownload($localFile, $remoteFile, $doWhileDownloading, $interval = 1, $mode = self::GET_TRANSFER_MODE
    ) {
        return $this->asyncDownload(
            $remoteFile,
            $doWhileDownloading,
            dirname($localFile),
            $interval,
            $mode,
            filesize($localFile)
        );
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

        // TODO sys_get_temp_dir()
        $tempFile = tempnam(sys_get_temp_dir(), $remoteFile);

        if ( ! $this->wrapper->get($tempFile, $remoteFile, self::ASCII)) {
            throw new ClientException(ClientException::getFtpServerError()
                ?: "Unable to get [{$remoteFile}] content."
            );
        }

        $content = file_get_contents($tempFile);
        unlink($tempFile);

        return $content;
    }

    /**
     * Extract the file type (type, dir, link) from chmod string
     * (e.g., 'drwxr-xr-x' will return 'dir').
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