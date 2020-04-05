<?php

namespace Lazzard\FtpClient\Configuration\Exception;

use Lazzard\FtpClient\Exception\FtpClientException;

/**
 * Class ExtensionException
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Exception
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class ExtensionException extends \RuntimeException implements FtpClientException
{
    public function __construct($message)
    {
        parent::__construct("[FTP Configuration Exception] " . $message);
    }
}