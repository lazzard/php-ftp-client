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
     * FtpClient predefined constants.
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
            array_unshift($arguments, parent::getConnection());
            return call_user_func_array($ftpFunction, $arguments);
        }

        throw new FtpClientLogicException("{$ftpFunction} is invalid FTP function.");
    }

    /**
     * Get files in giving directory.
     *
     * @param string $directory Target directory
     * @param bool   $ignoreDotes Ignore dots files items '.' and '..'
     * @param null   $callback Filtering returned files
     *
     * @return array
     *
     * @throws \Lazzard\FtpClient\Exception\FtpClientLogicException
     */
    public function getFiles($directory = null, $ignoreDotes = self::IGNORE_DOTS, $callback = null)
    {
        if (($list = ftp_nlist(parent::getConnection(), $directory ?: parent::getCurrentDir())) === false) {
            throw FtpClientRuntimeException::unreachableServerContent();
        }

        if ($ignoreDotes === true) {
            $list = array_filter($list, function ($item) {
                return !in_array($item, ['.', '..']);
            });
        }

        if (is_null($callback) === false) {
            if (is_callable($callback) === false) {
                throw new FtpClientLogicException("Invalid callback parameter passed to " . __FUNCTION__ . "() function.");
            }

            $list = array_filter($list, $callback);
        }

        return array_values($list);
    }

    public function getFilesOnly($directory = null, $ignoreDotes = self::IGNORE_DOTS)
    {
        return $this->extractRawList($this->getConnection(), $directory);
    }

    /**
     * Check weather if a file is a directory or not.
     *
     * @param $directory
     *
     * @return bool Return true if the giving file is a directory,
     * false if isn't or the file doesn't exists.
     */
    public function isDirectory($directory)
    {
        if (parent::getFtpWrapper()->chdir(parent::getConnection(), $directory) !== false) {
            parent::getFtpWrapper()->chdir(parent::getConnection(), parent::getCurrentDir());
            return true;
        }

        return false;
    }

    public function extractRawList($ftpStream, $fileName)
    {
        $info = [];

        # Check if the file is directory.
        $isDir = ftp_size($ftpStream, $fileName) === -1 ? true : false;

        # Throwing exception if rawlist fail to getting the file data.
        # If the file is a directory we back to the previous folder and lopping over it to get the dir info.
        if (empty($list = ftp_rawlist($ftpStream, $isDir ? $fileName . '/..' : $fileName)))
            throw new FtpClientRuntimeException("Unreachable server content.");

        # Extract date from rawlist result array.
        foreach ($list as $item) {
            # Clean the spaces.
            $clean = preg_replace('/\s+/', ' ', $item);

            # rawlist string to an array.
            $split_string = explode(" ", $clean);
            # file name.
            $name = $split_string[count($split_string) - 1];

            # Compare the file name from rawlist with the file name that we want information from.
            if ($name === ($isDir ? pathinfo($fileName, PATHINFO_BASENAME) : $fileName))
            {
                $info['chmod'] = $split_string[0];
                $info['num'] = $split_string[1];
                $info['owner'] = $split_string[2];
                $info['group'] = $split_string[3];
                $info['size'] = $split_string[4];
                $info['Mtime'] = sprintf("%s %s %s", $split_string[5], $split_string[6], $split_string[7]);
                $info['name'] = $split_string[8];
            }
        }

        return $info;
    }


}