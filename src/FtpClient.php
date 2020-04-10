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
     * Get files in giving directory.
     *
     * @param string $directory   Target directory
     * @param bool   $ignoreDotes Ignore dots files items '.' and '..'
     * @param null   $callback    Filtering returned files
     *
     * @return array
     */
    public function getFiles($directory = null, $ignoreDotes = self::IGNORE_DOTS, $callback = null)
    {
        $files = $this->getFtpWrapper()->nlist(
            $this->getConnection(),
            $directory ?: $this->getCurrentDir()
        );

        if ($ignoreDotes === true) {
            $files = array_filter($files, function ($item) {
                return !in_array($item, ['.', '..']);
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
     * Get files of type file from the giving directory.
     *
     * @param null $directory
     * @param bool $ignoreDotes
     * @param null $callback
     * 
     * @see getFiles()
     *
     * @return array
     */
    public function getFilesOnly($directory = null, $ignoreDotes = self::IGNORE_DOTS, $callback = null)
    {
        $files = $this->getFiles($directory ?: $this->getCurrentDir(), $ignoreDotes, $callback);

        $filesOnly = [];
        foreach ($files as $file) {
            if ($this->isDirectory($directory ?: $this->getCurrentDir() . '/' . $file) !== true) {
                $filesOnly[] = $file;
            }
        }

        return $filesOnly;
    }

    /**
     * Get only directories files.
     *
     * @param null $directory
     * @param bool $ignoreDotes
     * @param null $callback
     *
     * @see getFiles()
     * 
     * @return array
     */
    public function getDirsOnly($directory = null, $ignoreDotes = self::IGNORE_DOTS, $callback = null)
    {
        $files = $this->getFiles($directory ?: $this->getCurrentDir(), $ignoreDotes, $callback);

        $dirsOnly = [];
        foreach ($files as $file) {
            if ($this->isDirectory($directory ?: $this->getCurrentDir() . '/' . $file)) {
                $dirsOnly[] = $file;
            }
        }

        return $dirsOnly;
    }

    /**
     * Check weather if a file is a directory or not.
     *
     * @param string|null $directory
     *
     * @return bool Return true if the giving file is a directory,
     * false if isn't or the file doesn't exists.
     */
    public function isDirectory($directory = null)
    {
        $originalDir = $this->getCurrentDir();
        if ($this->getFtpWrapper()->chdir($this->getConnection(), $directory ?: $this->getCurrentDir()) !== false)
        {
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
     * @see \Lazzard\FtpClient\Command\FtpCommand::request()
     *
     * @throws \Lazzard\FtpClient\Exception\FtpClientRuntimeException
     */
    public function getFeatures()
    {
        if ($this->getFtpCommand()->rawRequest("FEAT") !== false) {
            return array_map('ltrim', $this->getFtpCommand()->getResponseBody());
        }

        throw new FtpClientRuntimeException("Cannot get remote server features.");
    }

    /**
     * Determine if the giving feature is supported by the remote server or not.
     *
     * @param string $feature
     *
     * @see \Lazzard\FtpClient\FtpClient::getFeatures()
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
     * @return string
     *
     * @throws \Lazzard\FtpClient\Exception\FtpClientRuntimeException
     */
    public function getSystem()
    {
        if ($this->getFtpCommand()->rawRequest("SYST") !== false) {
            return $this->getFtpCommand()->getResponseMessage();
        }

        throw new FtpClientRuntimeException("Cannot get remote server features.");
    }

}