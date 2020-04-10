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
class FtpConfigurationLogicException extends \LogicException implements FtpClientException
{
    public function __construct($message)
    {
        parent::__construct("[FtpConfiguration Exception] : " . $message);
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