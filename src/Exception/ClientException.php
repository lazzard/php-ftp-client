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
        parent::__construct(sprintf(
            "[FtpClient ERROR] - %s => %s:%s()\n<br>[FTP Server ERROR] - %s",
            $message,
            basename(debug_backtrace()[count(debug_backtrace()) - 1]['class']),
            debug_backtrace()[count(debug_backtrace()) - 1]['function'],
            error_get_last()['message']
        ));
    }
}