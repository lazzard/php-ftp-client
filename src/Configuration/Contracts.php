<?php

namespace Lazzard\FtpClient\Configuration;

use Lazzard\FtpClient\Configuration\Exception\FtpConfigurationException;

/**
 * Class Contracts
 *
 * Configuration options contracts.
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Configuration
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
final class Contracts
{
    /**
     * @param array $option
     *
     * @return bool
     */
    public static function validate($option)
    {
        switch (key($option))
        {
            case "timeout":
                self::isInt($option['timeout'], "Timeout must be an integer."); break;
            case "passive":
                self::isBool($option['passive'], "Passive option must be boolean value."); break;
        }

        return true;
    }

    /**
     * @param $value
     * @param $message
     *
     * @throws \Lazzard\FtpClient\Configuration\Exception\FtpConfigurationException
     */
    public static function isInt($value, $message)
    {
        if (is_int($value) === false)
            throw new FtpConfigurationException($message);
    }

    /**
     * @param $value
     * @param $message
     *
     * @throws \Lazzard\FtpClient\Configuration\Exception\FtpConfigurationException
     */
    public static function isBool($value, $message)
    {
        if (is_bool($value) === false)
            throw new FtpConfigurationException($message);
    }

}