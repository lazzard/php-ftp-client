<?php

namespace Lazzard\FtpClient\Exception;

/**
 * Class ClientException
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Exception
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class ClientException extends \Exception implements FtpClientException
{
    public function __construct($message)
    {
        parent::__construct(
            self::_classBaseName(get_class($this)) === self::_classBaseName(self::class)
            ? "[ClientException] - " . $message : $message
        );
    }

    public static function getFtpServerError()
    {
        return @explode(' ', error_get_last()['message'], 2)[1];
    }

    private static function _classBaseName($class)
    {
        return basename(str_replace('\\', '/', $class), $class);
    }
}