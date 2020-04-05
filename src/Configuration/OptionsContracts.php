<?php

namespace Lazzard\FtpClient\Configuration;

use Lazzard\FtpClient\Configuration\Exception\FtpConfigurationOptionException;

/**
 * Class OptionsContracts
 *
 * FtpConfiguration options contracts.
 *
 * @since 1.0
 * @package Lazzard\FtpClient\FtpConfiguration
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
final class OptionsContracts
{
    /**
     * @param array $option
     *
     * @return bool
     *
     * @throws \Lazzard\FtpClient\Configuration\Exception\FtpConfigurationOptionException
     */
    public static function isValidateOption($option)
    {
        switch (key($option))
        {
            case "timeout":
                return self::isInt($option['timeout']);

            case "passive":
                return self::isBool($option['passive']);

            default: return false;
        }
    }

    /**
     * @param $value
     *
     * @return bool
     *
     * @throws \Lazzard\FtpClient\Configuration\Exception\FtpConfigurationOptionException
     */
    public static function isInt($value)
    {
        if (is_int($value) === false)
            FtpConfigurationOptionException::optionValueMustBeAnInteger($value);

        return true;
    }

    /**
     * @param $value
     *
     * @return bool
     *
     * @throws \Lazzard\FtpClient\Configuration\Exception\FtpConfigurationOptionException
     */
    public static function isBool($value)
    {
        if (is_bool($value) === false)
            throw FtpConfigurationOptionException::optionValueMustBeABoolean($value);

        return true;
    }

}