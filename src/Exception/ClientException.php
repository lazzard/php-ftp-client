<?php

namespace Lazzard\FtpClient\Exception;

/**
 * Class ClientException
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Exception
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class ClientException extends \RuntimeException implements FtpClientException
{
    public function __construct($message)
    {
        parent::__construct(sprintf(
            "[FtpClient Exception] - %s => %s:%s()",
            $message,
            basename(debug_backtrace()[count(debug_backtrace()) - 1]['class']),
            debug_backtrace()[count(debug_backtrace()) - 1]['function']
        ));
    }
}