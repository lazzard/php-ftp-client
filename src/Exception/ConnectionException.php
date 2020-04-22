<?php

namespace Lazzard\FtpClient\Exception;

/**
 * Class CommandException
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Exception
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class ConnectionException extends \RuntimeException implements FtpClientException
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