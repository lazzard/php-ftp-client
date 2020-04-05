<?php

namespace Lazzard\FtpClient\Configuration\Exception;

use Lazzard\FtpClient\Exception\FtpClientException;

class ExtensionException extends \RuntimeException implements FtpClientException
{

    public static function phpExtensionNotLoaded($extension)
    {
        return new self("{$extension} extension not loaded.");
    }

    public static function notSupportedPhpExtension($extension)
    {
        return new self("{$extension} extension not supported.");
    }

}