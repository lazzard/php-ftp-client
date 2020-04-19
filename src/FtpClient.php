<?php

namespace Lazzard\FtpClient;

use Lazzard\FtpClient\Exception\ClientException;

/**
 * Class FtpClient
 *
 * @since 1.0
 * @package Lazzard\FtpClient
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class FtpClient extends FtpManager
{
    /**
     * FtpClient predefined constants
     */
    const DOTS = ['.', '..'];

    /**
     * FtpClient __call.
     *
     * Handle unsupportable FTP functions by FtpClient,
     * And call the alternative function if exists.
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     *
     * @throws ClientException
     */
    public function __call($name, $arguments)
    {
        $ftpFunction = "ftp_" . $name;

        if (function_exists($ftpFunction)) {
            array_unshift($arguments, $this->getConnection());
            return call_user_func_array($ftpFunction, $arguments);
        }

        throw new ClientException("{$ftpFunction} is invalid FTP function.");
    }

    /**
     * Check weather if a file is a directory or not.
     *
     * @param string $directory
     *
     * @return bool Return true if the giving file is a directory,
     *              false if isn't or the file doesn't exists.
     */
    public function isDirectory($directory)
    {
        $originalDir = $this->getCurrentDir();
        if ($this->ftpWrapper->chdir($this->getConnection(), $directory) !== false) {
            $this->ftpWrapper->chdir($this->getConnection(), $originalDir);
            return true;
        }

        return false;
    }

    /**
     * Extract the file type (type, dir, link) from chmod string
     * (e.g., 'drwxr-xr-x' string will return 'dir').
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

    /**
     * Get list of files names in giving directory.
     *
     * @param string   $directory             Target directory
     * @param bool     $ignoreDotes[optional] Ignore dots files items '.' and '..',
     *                                        default sets to false.
     *
     * @return array
     */
    public function listDirectory($directory, $ignoreDotes = true)
    {
        if ( ! $files = $this->ftpWrapper->nlist(
            $this->getConnection(),
            $directory
        )) {
            throw new ClientException("Failed to get files list.");
        }

        return $ignoreDotes ? array_slice($files, 2) : $files;
    }

    /**
     * Get files only from the giving directory.
     *
     * @see FtpClient::listDirectory()
     *
     * @param string   $directory             Target directory
     * @param bool     $ignoreDotes[optional] Ignore dots files items '.' and '..',
     *                                        default sets to false.
     *
     * @return array
     */
    public function getFilesOnly($directory, $ignoreDotes = true)
    {
        $files = $this->listDirectory(
            $directory,
            $ignoreDotes
        );

        $filesOnly = [];
        foreach ($files as $file) {
            if ($this->isDirectory(sprintf('%s/%s', $directory, $file))) {
                $filesOnly[] = $file;
            }
        }

        return $filesOnly;
    }


    /**
     * Get only the directories from the giving directory.
     *
     * @see FtpClient::listDirectory()
     *
     * @param string   $directory             Target directory
     * @param bool     $ignoreDotes[optional] Ignore dots files items '.' and '..',
     *                                        default sets to false
     *
     * @return array
     */
    public function getDirsOnly($directory, $ignoreDotes = true)
    {
        $files = $this->listDirectory(
            $directory,
            $ignoreDotes
        );

        $dirsOnly = [];
        foreach ($files as $file) {
            if ($this->isDirectory(sprintf('%s/%s', $directory, $file))) {
                $dirsOnly[] = $file;
            }
        }

        return $dirsOnly;
    }

    /**
     * Get detailed list of files in the giving directory.
     *
     * @param string $directory
     * @param bool   $recursive[optional]
     * @param bool   $ignoreDots[optional]
     *
     * @return array
     */
    public function listDirectoryDetails($directory, $recursive = false, $ignoreDots = true)
    {
        $details = $this->ftpWrapper->rawlist(
            $this->getConnection(),
            $directory,
            $recursive
        );

        $pathTmp = null;
        $info = [];
        foreach ($details as $detail) {
            $chunks = preg_split('/\s+/', $detail);

            if (strlen($chunks[0]) !== 0 && count($chunks) !== 9) {
                $splice = explode('/', substr($chunks[0], 0, -1));
                $pathTmp = join('/', $splice);
            }

            if (count($chunks) === 9) {
                if ($ignoreDots) {
                    if (in_array($chunks[8], self::DOTS)) {
                        continue;
                    }
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
                    'type'  => $this->_chmodToFileType($chunks[0]),
                    'path'  => $pathTmp ? $pathTmp . '/' . $chunks[8] : $directory . '/' . $chunks[8]
                ];
            }
        }

        return $info;
    }

    /**
     * Get files count of the giving directory.
     *
     * @see FtpClient::rawListDirectory()
     *
     * @param string $directory
     * @param bool   $recursive[optional]
     * @param bool   $ignoreDots[optional]
     *
     * @return int
     */
    public function getCount($directory, $recursive = false, $ignoreDots = false)
    {
        return count($this->listDirectoryDetails(
            $directory,
            $recursive,
            $ignoreDots
        ));
    }

    /**
     * Get supported remote server commands.
     *
     * @see FtpCommand::rawRequest()
     *
     * @return array
     *
     * @throws ClientException
     */
    public function getFeatures()
    {
        if (!$this->ftpCommand->rawRequest("FEAT")->isSucceeded()) {
            throw new ClientException("Cannot get remote server features.");
        }

        return array_map('ltrim', $this->ftpCommand->getResponseBody());
    }

    /**
     * Determine if the giving feature is supported by the remote server or not.
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
     * Get remote server system name.
     *
     * @see FtpCommand::rawRequest()
     *
     * @return string
     *
     * @throws ClientException
     */
    public function getSystem()
    {
        if (!$this->ftpCommand->rawRequest("SYST")->isSucceeded()) {
            throw new ClientException("Cannot get remote server features.");
        }

        return $this->ftpCommand->getResponseMessage();
    }

    /**
     * Get supported SITE commands by the remote server.
     *
     * @see FtpCommand::rawRequest()
     *
     * @return array Return array of SITE available commands in success.
     *
     * @throws ClientException
     */
    public function getSupportedSiteCommands()
    {
        if ( ! $this->ftpCommand->rawRequest("HELP")->isSucceeded()) {
            throw new ClientException("Cannot getting available site commands from the FTP server.");
        }

        return array_map('ltrim', $this->ftpCommand->getResponseBody());
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
        if ( ! $this->ftpWrapper->cdup($this->getConnection())) {
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
        if ( ! $this->isExists($remoteFile) || $this->isDirectory($remoteFile) ) {
            throw new ClientException("[{$remoteFile}] must be an existing file.");
        }

        if ( ! $this->ftpWrapper->delete($this->getConnection(), $remoteFile)) {
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
        if ($this->ftpWrapper->size($this->getConnection(), $directory) !== -1) {
            throw new ClientException(
                "[{$directory}] must be an existing directory."
            );
        }

        if ( ! ($list = $this->ftpWrapper->nlist($this->getConnection(), $directory))) {
            $this->removeDirectory($directory);
        }

        if ( ! empty($list)) {
            foreach ($list as $file) {
                $path = $directory . '/' . $file;

                if (in_array(basename($path), self::DOTS)) continue;

                if ($this->ftpWrapper->size($this->getConnection(), $path) !== -1) {
                    $this->ftpWrapper->delete($this->getConnection(), $path);
                } elseif ($this->ftpWrapper->rmdir($this->getConnection(), $path) !== true) {
                    $this->removeDirectory($path);
                }
            }
        }

        return $this->ftpWrapper->rmdir($this->getConnection(), $directory);
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
        if ($this->isDirectory($directory)) {
            throw new ClientException("[{$directory}] already exits.");
        }

        $dirs = explode('/', $directory);
        $count = count($dirs);

        for ($i = 1; $i <= $count; $i++) {
            $path = join("/", array_slice($dirs, 0, $i));

            if ( ! $this->isDirectory($path)) {
                $this->ftpWrapper->mkdir($this->getConnection(), $path);
            }
        }

        return true;
    }

    /**
     * Check weather if the giving file is exists or not.
     *
     * @param string $remoteFile
     *
     * @return bool
     */
    public function isExists($remoteFile)
    {
        $list = $this->ftpWrapper->nlist(
            $this->getConnection(),
            dirname($remoteFile)
        );

        return in_array(basename($remoteFile), $list);
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
        if ( ! $this->isFeatureSupported('MDTM')) {
            throw new ClientException("This feature not supported by the remote server.");
        }

        // TODO implementation for directories
        if ($this->isDirectory($remoteFile)) {
            throw new ClientException(sprintf(
                "%s() does not work with directories.",
                __FUNCTION__)
            );
        }

        if ($format) {
            return date($format, $this->ftpWrapper->mdtm($this->getConnection(), $remoteFile));
        }

        return $this->ftpWrapper->mdtm($this->getConnection(), $remoteFile);
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
        if ( ! $this->isFeatureSupported("SIZE")) {
            throw new ClientException("SIZE feature not provided by the remote server.");
        }

        if ( ! $this->isDirectory($remoteFile)) {
            throw new ClientException(
                "[{$remoteFile}] must be an existing file."
            );
        }

        return $this->ftpWrapper->size($this->getConnection(), $remoteFile);
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
        if ( ! $this->isFeatureSupported("SIZE")) {
            throw new ClientException(
                "SIZE feature not provided by the remote server."
            );
        }

        if ( ! $this->isDirectory($directory)) {
            throw new ClientException(
                "[{$directory}] must be an existing directory."
            );
        }

        $list = $this->listDirectoryDetails($directory, true);

        $size = 0;
        foreach ($list as $fileInfo) {
            $size += $this->ftpWrapper->size($this->getConnection(), $fileInfo['path']);
        }

        return $size;
    }

    /**
     * Check weather if the giving file/directory is empty or not.
     *
     * @param string $remoteFile
     *
     * @return bool
     */
    public function isEmpty($remoteFile)
    {
        if ($this->isDirectory($remoteFile)) {
            return empty($this->listDirectory($remoteFile, true));
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
            throw new ClientException(
                "[{$newName}] is already exists, please choose another name."
            );
        }

        if ( ! $this->ftpWrapper->rename($this->getConnection(), $oldName, $newName)) {
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
            throw new ClientException(
                "[{$source}] source file does not exists."
            );
        }

        if ( ! $this->isDirectory($destination)) {
            throw new ClientException(
                "[{$destination}] must be an existing directory."
            );
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
        return ($this->ftpCommand->rawRequest("NOOP")->getResponseCode() === 200);
    }
}