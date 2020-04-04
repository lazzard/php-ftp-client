<?php

namespace Lazzard\FtpClient\Exceptions;

/**
 * Class FtpClientLogicException
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Exceptions
 */
class FtpClientLogicException extends \LogicException implements FtpClientException {

    public static function invalidFtpFunction($ftpFunction)
    {
        return new \BadFunctionCallException(
            sprintf("[%s] is invalid FTP function.", $ftpFunction)
        );
    }

}