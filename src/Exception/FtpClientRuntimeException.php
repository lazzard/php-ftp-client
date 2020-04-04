<?php

namespace Lazzard\FtpClient\Exceptions;

/**
 * Class FtpClientRuntimeException
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Exceptions
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

}