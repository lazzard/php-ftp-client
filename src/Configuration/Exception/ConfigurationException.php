<?php

namespace Lazzard\FtpClient\Configuration\Exception;

use Lazzard\FtpClient\Exception\FtpClientException;

/**
 * Class FtpExtensionException
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Exception
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class ConfigurationException extends \RuntimeException implements FtpClientException
{
    /**
     * FtpExtensionException constructor.
     *
     * {@inheritDoc}
     */
    public function __construct($message)
    {
        parent::__construct("[FtpClient Configuration Exception] : " . $message);
    }
}