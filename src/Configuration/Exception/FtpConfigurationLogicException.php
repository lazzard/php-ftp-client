<?php

namespace Lazzard\FtpClient\Configuration\Exception;

use Lazzard\FtpClient\Exception\FtpClientException;

/**
 * Class FtpConfigurationLogicException
 *
 * @since 1.0
 * @package Lazzard\FtpClient\FtpConfiguration\Exception
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class FtpConfigurationLogicException extends \RuntimeException implements FtpClientException
{
    public function __construct($message)
    {
        parent::__construct("[FtpClient FtpConfiguration Exception] : " . $message);
    }

    public static function InvalidFtpConfigurationOption($option, $type)
    {
        return new self(sprintf(
            "%s option accept value of type %s",
                $option,
                $type
            ));
    }
}