<?php

namespace Lazzard\FtpClient\Configuration;

use Lazzard\FtpClient\Exception\ConfigurationException;

/**
 * Class FtpConfiguration
 *
 * @since 1.0
 * @package Lazzard\FtpClient\FtpConfiguration
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class FtpConfiguration implements Configurable
{
    /**
     * Predefined configurations by the ftp client.
     */
    const DEFAULT_CONF     = 'default';
    const RECOMMENDED_CONF = 'recommended';

    /** @var array */
    protected $config;

    /**
     * FtpConfiguration constructor.
     *
     * @param array|string $config
     *
     * @throws ConfigurationException
     */
    public function __construct($config)
    {
        $this->setConfig($config);
    }

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @inheritDoc
     */
    public function setConfig($config)
    {
        $importedConfig = include(__DIR__ . DIRECTORY_SEPARATOR . "Config.php");

        if (is_string($config)) {
            if ( ! key_exists($config, $importedConfig)) {
                throw new ConfigurationException(
                    "Cannot find configuration [{$config}] in the config file.");
            }
        }

        $this->config = $this->_validateTypeConstraints(
            is_string($config)
                ? $importedConfig[$config]
                : array_merge($importedConfig["default"], $config)
        );

        $this->_setPhpLimit($this->config['phpLimit']);
    }

    /**
     * @param int $integer
     *
     * @return bool
     */
    public function setMaxExecutionTime($integer)
    {
        var_dump($integer);
        return set_time_limit($integer);
    }

    /**
     * Returns the maximum execution time of the "current" script.
     *
     * @return int
     */
    public function getMaxExecutionTime()
    {
        return intval(ini_get('max_execution_time'));
    }

    /**
     * Validate config values types constraints.
     *
     * @param array $config
     *
     * @return array
     *
     * @throws ConfigurationException
     */
    protected function _validateTypeConstraints($config)
    {
        foreach ($config as $optionKey => $optionValue) {
            switch ($optionKey) {

                case "timeout":
                    if ( ! is_int($optionValue) || $optionValue <= 0) {
                        throw new ConfigurationException("[{$optionValue}] Timeout option value must be an integer and greater than 0.");
                    }
                    break;

                case "passive": case "usePassiveAddress": case "autoSeek":
                    if ( ! is_bool($optionValue)) {
                        throw new ConfigurationException("[{$optionKey}] option must have a boolean value.");
                    }
                    break;

                case "initialDirectory":
                    if ( ! is_string($optionValue)) {
                        throw new ConfigurationException("[{$optionKey}] option must have a string value.");
                    }
                    break;

                case "phpLimit":

                    foreach ($config['phpLimit'] as $limitKey => $limitValue) {

                        switch ($limitKey) {

                            case "maxExecutionTime":

                                if ( ! is_int($limitValue) && $limitValue !== NOT_CHANGE) {
                                    throw new ConfigurationException("[{$optionValue}] value must be of type integer.");
                                }
                                break;

                            default: throw new ConfigurationException("[{$limitKey}] is invalid php limit configuration option.");

                        }

                    }
                    break;

                default: throw new ConfigurationException("[{$optionKey}] is invalid configuration option.");
            }
        }

        return $config;
    }

    /**
     * Sets the config php limitations resources values.
     * 
     * @param $config
     *
     * @throws ConfigurationException
     */
    protected function _setPhpLimit($config)
    {
        if ( ! $this->setMaxExecutionTime($config['maxExecutionTime'])) {
            throw new ConfigurationException("Failed to set maximum execution time.");
        }
    }
}