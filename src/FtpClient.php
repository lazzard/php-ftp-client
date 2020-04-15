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
    public function getFilesList($directory, $ignoreDotes = self::IGNORE_DOTS, $callback = null)
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
     * Get files only from the giving directory.
     *
     * @see FtpClient::getFilesList()
     *
     * @param string   $directory             Target directory
     * @param bool     $ignoreDotes[optional] Ignore dots files items '.' and '..',
     *                                        default sets to false.
     * @param callable $callback[optional]    Filtering returned files with a callback
     *                                        function, default is null.
     *
     * @return array
     */
    public function getFilesOnly($directory, $ignoreDotes = self::IGNORE_DOTS, $callback =
    null)
    {
        $files = $this->getFilesList(
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
     * Get only the directories from the giving directory.
     *
     * @see FtpClient::getFilesList()
     *
     * @param string   $directory             Target directory
     * @param bool     $ignoreDotes[optional] Ignore dots files items '.' and '..',
     *                                        default sets to false.
     * @param callable $callback[optional]    Filtering returned files with a callback
     *                                        function, default is null.
     *
     * @return array
     */
    public function getDirsOnly($directory, $ignoreDotes = self::IGNORE_DOTS, $callback =
    null)
    {
        $files = $this->getFilesList(
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
    public function getFilesDetails($directory, $recursive = false, $ignoreDots = self::IGNORE_DOTS)
    {
        $details = $this->getFtpWrapper()->rawlist(
            $this->getConnection(),
            $directory,
            $recursive
        );

        $pathTmp = null;
        $info = [];
        foreach ($details as $detail) {
            $chunks = preg_split('/\s+/', $detail);

            if (strlen($chunks[0]) !== 0 && count($chunks) !== 9) {
                $pathTmp = substr($chunks[0], 0, -1);
            }

            if (count($chunks) === 9) {
                if ($ignoreDots === true) {
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
     * @see FtpClient::getFilesDetails()
     *
     * @param string $directory
     * @param bool   $recursive[optional]
     * @param bool   $ignoreDots[optional]
     *
     * @return int
     */
    public function getCount($directory, $recursive = false, $ignoreDots = self::IGNORE_DOTS)
    {
        return count($this->getFilesDetails(
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
        if ($this->getFtpWrapper()->chdir($this->getConnection(), $directory) !== false) {
            $this->getFtpWrapper()->chdir($this->getConnection(), $originalDir);
            return true;
        }

        return false;
    }

    /**
     * Get supported remote server features.
     *
     * @return array
     *
     * @see FtpCommand::rawRequest()
     *
     * @throws FtpClientRuntimeException
     */
    public function getFeatures()
    {
        if (!$this->getFtpCommand()->rawRequest("FEAT")->isSucceeded()) {
            throw new FtpClientRuntimeException("Cannot get remote server features.");
        }

        return array_map('ltrim', $this->getFtpCommand()->getResponseBody());
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
     * @see FtpCommand::rawRequest()
     *
     * @return string
     *
     * @throws FtpClientRuntimeException
     */
    public function getSystem()
    {
        if (!$this->getFtpCommand()->rawRequest("SYST")->isSucceeded()) {
            throw new FtpClientRuntimeException("Cannot get remote server features.");
        }

        return $this->getFtpCommand()->getResponseMessage();
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
        if (!$this->getFtpCommand()->rawRequest("HELP")->isSucceeded()) {
            throw new FtpClientRuntimeException("Cannot getting available site commands from the FTP server.");
        }

        return array_map('ltrim', $this->getFtpCommand()->getResponseBody());
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
        if ($this->getFtpWrapper()->cdup($this->getConnection()) !== true ) {
            throw new FtpClientRuntimeException("Cannot change to the parent directory.");
        }

        return true;
    }
}