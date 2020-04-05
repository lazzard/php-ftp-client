<?php

namespace Lazzard\FtpClient\Exception;

/**
 * Class FtpClientRuntimeException
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Exception
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class FtpClientRuntimeException extends \RuntimeException implements FtpClientException {

    public static function ftpServerConnectionFailed()
    {
        return new self("Connection failed to FTP server.");
    }

    public static function ftpServerLoggingFailed()
    {
        return new self("Logging failed to FTP server.");
    }

    public static function unreachableServerContent()
    {
        return new self("Unreachable server content.");
    }

    public static function closingFtpConnectionFailed()
    {
        return new self("Failed to close ftp connection.");
    }

}