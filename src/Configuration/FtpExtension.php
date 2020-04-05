<?php


namespace Lazzard\FtpClient\Configuration;

use Lazzard\FtpClient\Configuration\Exception\ExtensionException;
use Lazzard\FtpClient\Configuration\Utilities\ExtensionChecker;

/**
 * Class FtpExtension
 *
 * @since 1.0
 * @package Lazzard\FtpClient\FtpConfiguration
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
abstract class FtpExtension extends FtpOptions
{
    /**
     * FTP Client Required extensions.
     */
    const REQUIRED_EXTENSIONS = [
      "ftp",
    ];

    /**
     * FtpExtension constructor.
     *
     * Check if FTP extension is loaded before setting FTP options.
     *
     * @inheritDoc
     *
     * @throws \Lazzard\FtpClient\Configuration\Exception\ExtensionException
     */
    public function __construct($options = null)
    {
        foreach (self::REQUIRED_EXTENSIONS as $require_ext)
        {
            if (ExtensionChecker::isExists($require_ext) === false)
                throw ExtensionException::notSupportedPhpExtension($require_ext);

            if (ExtensionChecker::isLoaded($require_ext) === false)
                throw ExtensionException::phpExtensionNotLoaded($require_ext);
        }

        parent::__construct($options);
    }
}