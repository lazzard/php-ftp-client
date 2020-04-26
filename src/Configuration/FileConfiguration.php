<?php


namespace Lazzard\FtpClient\Configuration;

use Lazzard\FtpClient\Exception\ConfigurationException;

/**
 * Class FileConfiguration
 *
 * @since 1.0
 * @package Lazzard\FtpClient\FtpConfiguration
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
abstract class FileConfiguration
{
    /** @var array */
    protected $config;

    /** @var array */
    protected static $configFile;

    /**
     * Configuration constructor.
     *
     */
    public function __construct()
    {
        self::$configFile = self::$configFile ?: include(__DIR__ . DIRECTORY_SEPARATOR . "Config.php");
    }
    
    /**
     * Retrieve configuration options as an array from the config file.
     * 
     * @return array
     */
    abstract public function getConfig();

    /**
     * Sets the provided configuration.
     * 
     * @param array $config
     */
    abstract public function setConfig($config);

    /**
     * Setting the configuration options.
     *
     * @return void
     */
    abstract public function apply();

    /**
     * Validate configuration options values.
     *
     * @return bool
     *
     * @throws ConfigurationException
     */
    abstract protected function _validateConfiguration();

}