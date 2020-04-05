<?php

namespace Lazzard\FtpClient\Configuration\Utilities;

use Lazzard\FtpClient\Configuration\Exception\FtpOptionException;

/**
 * Class OptionsContracts
 *
 * FtpConfiguration options contracts.
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Configuration\Utilities
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
final class FtpOptionsContracts
{
    /**
     * @param array $option
     *
     * @return bool
     *
     * @throws \Lazzard\FtpClient\Configuration\Exception\FtpOptionException
     */
    public static function validate($option)
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
     * @throws \Lazzard\FtpClient\Configuration\Exception\FtpOptionException
     */
    private static function isInt($value)
    {
        if (is_int($value) === false)
            FtpOptionException::optionValueMustBeAnInteger($value);

        return true;
    }

    /**
     * @param $value
     *
     * @return bool
     *
     * @throws \Lazzard\FtpClient\Configuration\Exception\FtpOptionException
     */
    private static function isBool($value)
    {
        if (is_bool($value) === false)
            throw FtpOptionException::optionValueMustBeABoolean($value);

        return true;
    }

}