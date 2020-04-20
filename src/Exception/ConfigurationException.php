<?php

namespace Lazzard\FtpClient\Exception;


/**
 * Class ConfigurationException
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Exception
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class ConfigurationException extends \Exception implements FtpClientException
{
    public function __construct($message)
    {
        parent::__construct("[FtpConfiguration Exception] - " . $message);
    }

    public static function InvalidFtpConfigurationSetting($setting, $type)
    {
        return new \InvalidArgumentException(sprintf(
            "%s setting accept value of type %s",
            $setting,
            $type
        ));
    }

}