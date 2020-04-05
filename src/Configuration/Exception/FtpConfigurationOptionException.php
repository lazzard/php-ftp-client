<?php

namespace Lazzard\FtpClient\Configuration\Exception;

use Lazzard\FtpClient\Exception\FtpClientException;

/**
 * Class FtpConfigurationOptionException
 *
 * @since 1.0
 * @package Lazzard\FtpClient\FtpConfiguration\Exception
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class FtpConfigurationOptionException extends \InvalidArgumentException implements FtpClientException
{

    public static function invalidConfigurationOption($option)
    {
        return new self("{$option} is invalid FTP configuration option.");
    }

    public static function optionValueMustBeAnInteger($optionValue)
    {
        return new self("{$optionValue} must be an integer.");
    }

    public static function optionValueMustBeABoolean($optionValue)
    {
        return new self("{$optionValue} must be boolean value.");
    }

}