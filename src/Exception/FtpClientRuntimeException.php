<?php

namespace Lazzard\FtpClient\Exception;

/**
 * Class FtpClientRuntimeException
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Exception
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class FtpClientRuntimeException extends \RuntimeException implements FtpClientException
{
    public function __construct($message)
    {
        $target = debug_backtrace()[count(debug_backtrace()) - 1]['function'];
        parent::__construct("[FtpClient Exception] " . $message . ' => ' . $target);
    }

    public static function unreachableServerContent()
    {
        return new self("Unreachable server content, try to increase the FTP timeout.");
    }
}