<?php

namespace Lazzard\FtpClient;

use Lazzard\FtpClient\Command\FtpCommand;
use Lazzard\FtpClient\Configuration\FtpConfiguration;
use Lazzard\FtpClient\Connection\FtpConnection;
use Lazzard\FtpClient\Connection\ConnectionInterface;
use Lazzard\FtpClient\Exception\ClientException;

/**
 * Class FtpClient
 *
 * @since 1.0
 * @package Lazzard\FtpClient
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class FtpClient
{
    /**
     * FtpClient predefined constants
     */
    const FILE_DIR_TYPE = 0;
    const FILE_TYPE     = 2;
    const DIR_TYPE      = 1;

    /** @var FtpConnection */
    protected $connection;

    /** @var FtpConfiguration */
    protected $configuration;

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
     * @return FtpConnection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param FtpConnection $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return FtpConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param FtpConfiguration $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
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
            throw new ClientException("Cannot change current directory to [{$directory}].");
        }

        $this->currentDir = $directory;
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
        $originalDir = $this->getCurrentDir();
        if ($this->wrapper->chdir($directory) !== false) {
            $this->wrapper->chdir($originalDir);
            return true;
        }

        return false;
    }

    /**
     * Gets parent of the current directory.
     *
     * @return string
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
     * Get list of files names in giving directory.
     *
     * @param string $directory               Target directory
     * @param int    $filter
     * @param bool   $ignoreDots             [optional] Ignore dots files items '.' and '..',
     *                                        default sets to false.
     *
     * @return array
     */
    public function listDirectory($directory, $filter = self::FILE_DIR_TYPE, $ignoreDots = true)
    {
        if ( ! $files = $this->wrapper->nlist($directory)) {
            throw new ClientException("Failed to get files list.");
        }

        if ($ignoreDots) {
            $files = array_slice($files, 2);
        }

        switch ($filter) {

            case self::DIR_TYPE:
                return array_filter($files, function ($file){
                   return $this->isDir($file);
                });

            case self::FILE_TYPE:
                return array_filter($files, function ($file){
                    return ! $this->isDir($file);
                });

            default: return $files;
        }
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
     */
    public function listDirectoryDetails($directory, $recursive = false, $filter = self::FILE_DIR_TYPE, $ignoreDots = true)
    {
        if ( ! $this->isDir($directory)) {
            throw new ClientException("[{$directory}] is not a directory.");
        }
        
        if ( ! ($details = $this->wrapper->rawlist($directory, $recursive))) {
            throw new ClientException("Unable to get files list for [{$directory}] directory");
        }

        $pathTmp = null;
        $info = [];
        foreach ($details as $detail) {
            $chunks = preg_split('/\s+/', $detail);

            if (strlen($chunks[0]) !== 0 && count($chunks) !== 9) { // catch directory path
                $splice = explode('/', substr($chunks[0], 0, -1));
                $pathTmp = join('/', $splice);
            }

            if (count($chunks) === 9) {
                $type = $this->_chmodToFileType($chunks[0]);

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
     * Get files count of the giving directory.
     *
     * @see FtpClient::listDirectoryDetails()
     *
     * @param string $directory
     * @param bool   $recursive  [optional]
     * @param int    $filter
     * @param bool   $ignoreDots [optional]
     *
     * @return int
     */
    public function getCount($directory, $recursive = false, $filter = self::FILE_DIR_TYPE,
        $ignoreDots = false)
    {
        return count($this->listDirectoryDetails(
            $directory,
            $recursive,
            $filter,
            $ignoreDots
        ));
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
            throw new ClientException("Cannot get remote server features.");
        }

        return array_map('ltrim', $this->command->getResponseBody());
    }

    /**
     * Determine if the giving feature is supported by the remote server or not.
     *
     * Note : the characters case not important.
     *
     * @see FtpClient::getFeatures()
     *
     * @param string $feature
     *
     * @return bool
     */
    public function isFeatureSupported($feature)
    {
        return in_array(
            strtolower($feature),
            array_map('strtolower', $this->getFeatures())
        );
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
            throw new ClientException("Unable to get operating system type.");
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
            throw new ClientException("Cannot get remote server features.");
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
            throw new ClientException("Cannot getting available site commands from the FTP server.");
        }

        return array_map('ltrim', $this->command->getResponseBody());
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
            throw new ClientException("Unable to change to the parent directory.");
        }

        return true;
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
        if ( ! $this->isExists($remoteFile) || $this->isDir($remoteFile) ) {
            throw new ClientException("[{$remoteFile}] must be an existing file.");
        }

        if ( ! $this->wrapper->delete($remoteFile)) {
            throw new ClientException("Unable to delete the file [{$remoteFile}].");
        }

        return true;
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
     */
    public function createDirectory($directory)
    {
        if ($this->isExists($directory)) {
            throw new ClientException("[{$directory}] already exists.");
        }

        $dirs = explode('/', $directory);
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
     * Gets last modified time for an FTP remote file.
     *
     * @param string      $remoteFile
     * @param string|null $format[optional]
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
            throw new ClientException("Could not get last modified time for [{$remoteFile}].");
        }
        
        return $format ? date($format, $time) : $time;
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
    public function fileSize($remoteFile) {
        // TODO consider to remove this
        if ( ! $this->isFeatureSupported("SIZE")) {
            throw new ClientException("SIZE feature not provided by the remote server.");
        }

        if ( ! $this->isDir($remoteFile)) {
            throw new ClientException("[{$remoteFile}] must be an existing file.");
        }

        return $this->wrapper->size($remoteFile);
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
    public function dirSize($directory) {
        // TODO consider to remove this
        if ( ! $this->isFeatureSupported("SIZE")) {
            throw new ClientException("SIZE feature not provided by the remote server.");
        }

        if ( ! $this->isDir($directory)) {
            throw new ClientException("[{$directory}] must be an existing directory.");
        }

        $list = $this->listDirectoryDetails($directory, true);

        $size = 0;
        foreach ($list as $fileInfo) {
            $size += $this->wrapper->size($fileInfo['path']);
        }

        return $size;
    }

    /**
     * Check weather if the giving directory is empty or not.
     *
     * @param string $directory
     *
     * @return bool
     */
    public function isEmptyDir($directory)
    {
        if ( ! $this->isDir($directory)) {
            throw new ClientException("[{$directory}] is not directory.");
        }

        return empty($this->listDirectory($directory, true));
    }

    /**
     * Checks if the remote file is empty or not.
     *
     * @param $remoteFile
     *
     * @return bool
     */
    public function isEmptyFile($remoteFile) 
    {
        if ($this->isDir($remoteFile)) {
            throw new ClientException("[{$remoteFile}] is a directory.");
        }
        
        return ($this->fileSize($remoteFile) === 0);
    }

    /**
     * Rename a file/directory.
     *
     * @param string $oldName
     * @param string $newName
     *
     * @return bool
     */
    public function rename($oldName, $newName)
    {
        if ($this->isExists($newName)) {
            throw new ClientException("[{$newName}] is already exists, please choose another name.");
        }

        if ( ! $this->wrapper->rename($oldName, $newName)) {
            throw new ClientException(sprintf(
                "Unable to rename %s to %s",
                $oldName,
                $newName
            ));
        }

        return true;
    }

    /**
     * Move a file or a directory to another path.
     *
     * @param string $source      Source file
     * @param string $destination Destination directory
     *
     * @return bool
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
     * Check if the FTP server is still connected and responds for commands.
     *
     * @return bool
     */
    public function isServerAlive()
    {
        return $this->command->rawRequest("NOOP")->isSucceeded();
    }

    /**
     * Extract the file type (type, dir, link) from chmod string
     * (e.g., 'drwxr-xr-x' will return 'dir').
     *
     * @param string $chmod
     *
     * @return string
     */
    protected function _chmodToFileType($chmod)
    {
        switch ($chmod[0])
        {
            case '-':
                return 'file';

            case 'd':
                return 'dir';

            case 'l':
                return 'link';

            default: return 'unknown file type.';
        }
    }
}