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
        parent::__construct("[FtpClient Exception] " . $message);
    }

    public static function unreachableServerContent()
    {
        return new self("Unreachable server content, try to increase the FTP timeout.");
    }
}