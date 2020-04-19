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
        parent::__construct("[ConnectionException] - " . $message);
    }
}