<?php

namespace Lazzard\FtpClient;

use Lazzard\FtpClient\Exception\FtpClientLogicException;

/**
 * Class FtpClient
 *
 * @since 1.0
 * @package Lazzard\FtpClient
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class FtpClient extends FtpClientDriver
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
    public function getFiles($directory, $ignoreDotes = self::IGNORE_DOTS, $callback = null)
    {
        $list = ftp_nlist(parent::getConnection(), "$directory");

        if ($ignoreDotes === true) {
            $list = array_filter($list, function ($item) {
                if (in_array($item, ['.', '..']) === false) {
                    return true;
                }
                return false;
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

}