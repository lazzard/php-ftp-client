<?php

namespace Lazzard\FtpClient;

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
    const IGNORE_DOTS = false;
    const DOTS        = ['.', '..'];

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
     * @throws \Lazzard\FtpClient\Exception\FtpClientLogicException
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
    public function getDirectoryFiles($directory, $ignoreDotes = self::IGNORE_DOTS, $callback = null)
    {
        $files = $this->getFtpWrapper()->nlist(
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

        return array_values($files);
    }

    /**
     * Get files only (not the directories) from the giving directory.
     *
     * @see \Lazzard\FtpClient\FtpClient::getDirectoryFiles()
     *
     * @param string   $directory             Target directory
     * @param bool     $ignoreDotes[optional] Ignore dots files items '.' and '..',
     *                                        default sets to false.
     * @param callable $callback[optional]    Filtering returned files with a callback
     *                                        function, default is null.
     *
     * @return array
     */
    public function getDirectoryFilesOnly($directory, $ignoreDotes = self::IGNORE_DOTS, $callback =
    null)
    {
        $files = $this->getDirectoryFiles(
            $directory,
            $ignoreDotes,
            $callback
        );

        $filesOnly = [];
        foreach ($files as $file) {
            if ($this->isDirectory(sprintf('%s/%s', $directory, $file)) !== true) {
                $filesOnly[] = $file;
            }
        }

        return $filesOnly;
    }


    /**
     * Get only the directories.
     *
     * @see \Lazzard\FtpClient\FtpClient::getDirectoryFiles()
     *
     * @param string   $directory             Target directory
     * @param bool     $ignoreDotes[optional] Ignore dots files items '.' and '..',
     *                                        default sets to false.
     * @param callable $callback[optional]    Filtering returned files with a callback
     *                                        function, default is null.
     *
     * @return array
     */
    public function getDirectoryDirsOnly($directory, $ignoreDotes = self::IGNORE_DOTS, $callback =
    null)
    {
        $files = $this->getDirectoryFiles(
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
     * @param bool   $recursive
     *
     * @return array
     */
    public function getDirectoryDetails($directory, $recursive = false)
    {
        $details = $this->getFtpWrapper()->rawlist(
            $this->getConnection(),
            $directory,
            $recursive
        );

        $pathTmp = null;
        $info = [];
        foreach ($details as $detail) {
            $cleanDetail = preg_split('/\s+/', $detail);

            if (strlen($cleanDetail[0]) != 0 && count($cleanDetail) != 9) {
                $pathTmp = substr($cleanDetail[0], 0, -1);
            }

            if (count($cleanDetail) == 9) {
                $info[] = [
                    'name'  => $cleanDetail[8],
                    'chmod' => $cleanDetail[0],
                    'num'   => $cleanDetail[1],
                    'owner' => $cleanDetail[2],
                    'group' => $cleanDetail[3],
                    'size'  => $cleanDetail[4],
                    'month' => $cleanDetail[5],
                    'day'   => $cleanDetail[6],
                    'time'  => $cleanDetail[7],
                    'type'  => $this->_chmodToFileType($cleanDetail[0]),
                    'path'  => $pathTmp ? $pathTmp . '/' . $cleanDetail[8] : $cleanDetail[8]
                ];
            }
            
        }

        return $info;
    }

    /**
     * Get files count of the giving directory.
     *
     * @see \Lazzard\FtpClient\FtpClient::getDirectoryDetails()
     *
     * @param string $directory
     * @param bool   $recursive
     *
     * @return int
     */
    public function getDirectoryCount($directory, $recursive = false)
    {
        return count($this->getDirectoryDetails(
            $directory,
            $recursive)
        );
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
        if ($this->getFtpWrapper()->chdir($this->getConnection(), $directory) !== false)
        {
            $this->getFtpWrapper()->chdir($this->getConnection(), $originalDir);
            return true;
        }

        return false;
    }

    /**
     * Get supported remote server features.
     *
     * @see \Lazzard\FtpClient\Command\FtpCommand::rawRequest()
     *
     * @return array
     *
     * @throws \Lazzard\FtpClient\Exception\FtpClientRuntimeException
     */
    public function getFeatures()
    {
        if ($this->getFtpCommand()->rawRequest("FEAT") !== true) {
            throw new FtpClientRuntimeException("Cannot get remote server features.");
        }

        return array_map('ltrim', $this->getFtpCommand()->getResponseBody());
    }

    /**
     * Determine if the giving feature is supported by the remote server or not.
     *
     * @see \Lazzard\FtpClient\FtpClient::getFeatures()
     *
     * @param string $feature
     *
     * @return bool
     */
    public function isFeatureSupported($feature)
    {
        $featsCaseInsensitive = array_map(
            function ($item) {
                ltrim(strtolower($item));
                return true;
            },
            $this->getFeatures()
        );

        return in_array(strtolower($feature), $featsCaseInsensitive);
    }

    /**
     * Get remote server system name.
     *
     * @see \Lazzard\FtpClient\Command\FtpCommand::rawRequest()
     *
     * @return string
     *
     * @throws \Lazzard\FtpClient\Exception\FtpClientRuntimeException
     */
    public function getSystem()
    {
        if ($this->getFtpCommand()->rawRequest("SYST") !== true) {
            throw new FtpClientRuntimeException("Cannot get remote server features.");
        }

        return $this->getFtpCommand()->getResponseMessage();
    }

    /**
     * Get supported SITE commands by the remote server.
     *
     * @see \Lazzard\FtpClient\Command\FtpCommand::rawRequest()
     *
     * @return array Return array of SITE available commands in success.
     *
     * @throws \Lazzard\FtpClient\Exception\FtpClientRuntimeException
     */
    public function getSupportedSiteCommands()
    {
        if (($this->getFtpCommand()->rawRequest("HELP")) !== true) {
            throw new FtpClientRuntimeException("Cannot getting available site commands from the FTP server.");
        }

        return array_map('ltrim', $this->getFtpCommand()->getResponseBody());
    }


    /**
     * Back to the parent directory.
     *
     * @return bool
     *
     * @throws \Lazzard\FtpClient\Exception\FtpClientRuntimeException
     */
    public function back()
    {
        if (($this->getFtpWrapper()->cdup($this->getConnection())) !== true ) {
            throw new FtpClientRuntimeException("Cannot change to the parent directory.");
        }

        return true;
    }
}