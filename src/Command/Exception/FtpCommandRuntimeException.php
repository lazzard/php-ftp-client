<?php


namespace Lazzard\FtpClient\Command\Exception;

use Lazzard\FtpClient\Exception\FtpClientException;

/**
 * Class FtpCommandRuntimeException
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Command\Exception
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class FtpCommandRuntimeException extends \RuntimeException implements FtpClientException
{
    public function __construct($message)
    {
        parent::__construct("[FtpCommand Exception] : " . $message);
    }
}