<?php


namespace Lazzard\FtpClient\Configuration\Utilities;

/**
 * Class ExtensionChecker
 *
 * @since 1.0
 * @package Lazzard\FtpClient\FtpConfiguration
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
final class ExtensionChecker
{
    /**
     * Check weather if an Php extension is exists.
     *
     * Notice that if the extension not loaded this function will return false.
     *
     * @param $extension
     *
     * @return bool
     */
    public static function isExists($extension)
    {
        return  in_array($extension, get_loaded_extensions(), false);
    }

    /**
     * Check if an extension is loaded.
     *
     * @param $extension
     *
     * @return bool
     */
    public static function isLoaded($extension)
    {
        return extension_loaded($extension);
    }
}