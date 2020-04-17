<?php

namespace Lazzard\FtpClient;

use Lazzard\FtpClient\Command\Exception\FtpCommandRuntimeException;
use Lazzard\FtpClient\Exception\FtpClientLogicException;
use Lazzard\FtpClient\Exception\FtpClientRuntimeException;

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
     * @throws FtpClientLogicException
     */
    public function __call($name, $arguments)
    {
        $ftpFunction = "ftp_" . $name;

        if (function_exists($ftpFunction)) {
            array_unshift($arguments, $this->getConnection());
            return call_user_func_array($ftpFunction, $arguments);
        }

        throw new FtpClientLogicException("{$ftpFunction} is invalid FTP function.");
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
     * This method depends mainly on ftp_nlist function.
     *
     * @param string   $directory             Target directory
     * @param bool     $ignoreDotes[optional] Ignore dots files items '.' and '..',
     *                                        default sets to false.
     * @param callable $callback[optional]    Filtering returned files with a callback
     *                                        function, default is null.
     *
     * @return array
     */
    public function listDirectory($directory, $ignoreDotes = false, $callback = null)
    {
        $files = $this->ftpWrapper->nlist(
            $this->getConnection(),
            $directory
        );

        if ($ignoreDotes === true) {
            $files = array_filter($files, function ($item) {
                return !in_array(pathinfo($item, PATHINFO_BASENAME), self::DOTS);
            });
        }

        if (is_null($callback) === false) {
            if (is_callable($callback) === false) {
                throw new FtpClientLogicException("Invalid callback parameter passed to " . __FUNCTION__ . "() function.");
            }

            $files = array_filter($files, $callback);
        }

        return !empty($files) ? array_values($files) : $files;
    }

    /**
     * Get files only from the giving directory.
     *
     * @see FtpClient::listDirectory()
     *
     * @param string   $directory             Target directory
     * @param bool     $ignoreDotes[optional] Ignore dots files items '.' and '..',
     *                                        default sets to false.
     * @param callable $callback[optional]    Filtering returned files with a callback
     *                                        function, default is null.
     *
     * @return array
     */
    public function getFilesOnly($directory, $ignoreDotes = false, $callback =
    null)
    {
        $files = $this->listDirectory(
            $directory,
            $ignoreDotes,
            $callback
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
     *                                        default sets to false.
     * @param callable $callback[optional]    Filtering returned files with a callback
     *                                        function, default is null.
     *
     * @return array
     */
    public function getDirsOnly($directory, $ignoreDotes = false, $callback =
    null)
    {
        $files = $this->listDirectory(
            $directory,
            $ignoreDotes,
            $callback
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
     * This method depends mainly on the ftp_rawlist function.
     *
     * @param string $directory
     * @param bool   $recursive[optional]
     * @param bool   $ignoreDots[optional]
     *
     * @return array
     */
    public function rawListDirectory($directory, $recursive = false, $ignoreDots = false)
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
                $pathTmp = join(
                    '/', 
                    array_splice($splice, 1)
                );
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
                    'path'  => $pathTmp ? $pathTmp . '/' . $chunks[8] : $chunks[8]
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
        return count($this->rawListDirectory(
            $directory,
            $recursive,
            $ignoreDots
        ));
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
     * Get supported remote server commands.
     *
     * @see FtpCommand::rawRequest()
     *
     * @return array
     *
     * @throws FtpClientRuntimeException
     */
    public function getFeatures()
    {
        if (!$this->ftpCommand->rawRequest("FEAT")->isSucceeded()) {
            throw new FtpClientRuntimeException("Cannot get remote server features.");
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
     * @throws FtpClientRuntimeException
     */
    public function getSystem()
    {
        if (!$this->ftpCommand->rawRequest("SYST")->isSucceeded()) {
            throw new FtpClientRuntimeException("Cannot get remote server features.");
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
     * @throws FtpClientRuntimeException
     */
    public function getSupportedSiteCommands()
    {
        if (!$this->ftpCommand->rawRequest("HELP")->isSucceeded()) {
            throw new FtpClientRuntimeException("Cannot getting available site commands from the FTP server.");
        }

        return array_map('ltrim', $this->ftpCommand->getResponseBody());
    }


    /**
     * Back to the parent directory.
     *
     * @return bool
     *
     * @throws FtpClientRuntimeException
     */
    public function back()
    {
        if ($this->ftpWrapper->cdup($this->getConnection()) !== true ) {
            throw new FtpClientRuntimeException("Unable to change to the parent directory.");
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
     * @throws FtpClientRuntimeException
     */
    public function removeFile($remoteFile)
    {
        if ($this->isDirectory($remoteFile)) {
            throw new FtpClientRuntimeException("[{$remoteFile}] must be a file.");
        }

        if (!$this->isExists($remoteFile)) {
            throw new FtpClientRuntimeException("[{$remoteFile}] does not exists.");
        }

        if ($this->ftpWrapper->delete($this->getConnection(), $remoteFile) !== true) {
            throw new FtpClientRuntimeException("Unable to delete the file [{$remoteFile}].");
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
     * @throws FtpClientRuntimeException
     */
    public function removeDirectory($directory)
    {
        if (!$this->isExists($directory)) {
            throw new FtpClientRuntimeException("[{$directory}] does not exists.");
        }

        if (!$this->isDirectory($directory)) {
            throw new FtpClientRuntimeException("[{$directory}] must be a directory.");
        }

        $list = $this->listDirectory($directory);
        foreach ($list as $file) {
            $path = $directory . '/' . $file;

            if(in_array(basename($path), self::DOTS)) continue;

            if (!$this->isDirectory($path)) {
                $this->ftpWrapper->delete($this->getConnection(), $path);
            } elseif ($this->ftpWrapper->rmdir($this->getConnection(), $path) !== true) {
                $this->removeDirectory($path);
            }


        }

        $this->ftpWrapper->rmdir($this->getConnection(), $directory);

        return true;
    }

    public $temp = null;
    public function removeDir($directory)
    {
/*
        if (!$this->isExists($directory)) {
            throw new FtpClientRuntimeException("[{$directory}] does not exists.");
        }*/

        if (ftp_size($this->getConnection(), $directory) !== -1) {
            throw new FtpClientRuntimeException("[{$directory}] must be a directory.");
        }

        if (is_null($this->temp)) {
            $this->temp = $directory;
        }

        $list = $this->listDirectory($directory);
        if (!empty($list)) {
            foreach ($list as $file) {
                $path = $directory . '/' . $file;

                if (in_array(basename($path), self::DOTS)) {
                    continue;
                }

                if (ftp_size($this->getConnection(), $path) !== -1) {
                    $this->ftpWrapper->delete($this->getConnection(), $path);
                } elseif ($this->ftpWrapper->rmdir($this->getConnection(), $path) !== true) {
                    $this->removeDir($path);
                }

                if (empty($this->listDirectory($directory))) {
                    $this->removeDir($directory);
                }
            }
        }

        $this->ftpWrapper->rmdir($this->getConnection(), $directory);

        return true;
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
            throw new FtpCommandRuntimeException("[{$directory}] already exits.");
        }

        $folders = explode('/', $directory);
        $count = count($folders);
        for ($i = 1; $i <= $count; $i++) {
            $parts = array_splice($folders, 0, $i);
            $folders = array_merge($parts, $folders);

            $path = join("/", $parts);

            if (!$this->isDirectory($path)) {
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

        if (!empty($list)) {
            return in_array(basename($remoteFile), $list);
        }

        return false;
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
     * @throws FtpCommandRuntimeException
     */
    public function lastMTime($remoteFile, $format = null)
    {
        if (!$this->isFeatureSupported('MDTM')) {
            throw new FtpClientRuntimeException("This feature not supported by the remote server.");
        }
        
        if ($this->isDirectory($remoteFile)) {
            throw new FtpClientRuntimeException(sprintf(
                "%s() does not work with directories.",
                __FUNCTION__));
        }

        if ($format) {
            return date($format, $this->ftpWrapper->mdtm($this->getConnection(), $remoteFile));
        }

        return $this->ftpWrapper->mdtm($this->getConnection(), $remoteFile);
    }
}