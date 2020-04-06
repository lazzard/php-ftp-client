<?php

namespace Lazzard\FtpClient\Configuration\Utilities;

/**
 * Class FtpOptionValidator
 *
 * @since 1.0
 * @package Lazzard\FtpClient
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
final class FtpOptionValidator
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
                return is_int($option['timeout']);

            case "passive":
                return is_bool($option['passive']);

            case "autoSeek":
                return is_bool($option['autoSeek']);

            default: return false;
        }
    }
}