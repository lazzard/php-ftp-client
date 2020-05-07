<?php
/**
 * This file is part of the Lazzard/php-ftp-client package.
 *
 * (c) El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lazzard\FtpClient\Configuration;

use Lazzard\FtpClient\Exception\ConfigurationException;

/**
 * FileConfiguration class defines the behavior of all extended configuration classes.
 *
 * @since 1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
abstract class FileConfiguration
{
    /** @var array */
    protected $config;

    /** @var array */
    protected static $configFile;

    /**
     * Configuration constructor.
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
     * Merge the provided configuration with the appropriate configuration in the config file.
     *
     * @param $config
     *
     * @return array
     */
    abstract protected function merge($config);

    /**
     * Validate configuration options.
     *
     * @return bool
     *
     * @throws ConfigurationException
     */
    abstract protected function validateConfiguration();

    /**
     * @param $name
     *
     * @return array|false
     */
    protected function getConfigByName($name)
    {
        if ( ! array_key_exists($name, self::$configFile)) {
            return false;
        }

        return self::$configFile[$name];
    }

}