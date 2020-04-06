<?php

namespace Lazzard\FtpClient\Exception;

/**
 * Class FtpClientLogicException
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Exception
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class FtpClientLogicException extends \LogicException implements FtpClientException
{
    public function __construct($message)
    {
        parent::__construct("[FtpClient Exception] " . $message);
    }
}