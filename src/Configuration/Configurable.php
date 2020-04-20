<?php

namespace Lazzard\FtpClient\Configuration;

use Lazzard\FtpClient\Exception\ConfigurationException;

/**
 * Interface Configurable
 *
 * @since 1.0
 * @package Lazzard\FtpClient\FtpConfiguration
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
interface Configurable
{

    /**
     * @return array
     */
    public function getConfig();

    /**
     * @param array $config
     *
     * @throws ConfigurationException
     */
    public function setConfig($config);

}