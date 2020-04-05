<?php

namespace Lazzard\FtpClient\Exception;

/**
 * Class FtpClientLogicException
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Exception
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class FtpClientLogicException extends \LogicException implements FtpClientException {

    public static function invalidFtpFunction($ftpFunction)
    {
        return new \BadFunctionCallException("{$ftpFunction} is invalid FTP function.");
    }

    public static function invalidFtpResource()
    {
        return new self("Invalid Ftp resource.");
    }

}