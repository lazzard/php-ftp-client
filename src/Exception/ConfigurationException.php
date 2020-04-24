<?php

namespace Lazzard\FtpClient\Exception;


/**
 * Class ConfigurationException
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Exception
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class ConfigurationException extends ClientException
{
    public function __construct($message)
    {
        parent::__construct("[ConfigurationException] - " . $message);
    }

}