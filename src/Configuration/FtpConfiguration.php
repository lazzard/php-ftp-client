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
    /** @var array */
    private $config;

    /**
     * FtpConfiguration constructor.
     *
     * @param array|string $config
     *
     * @throws ConfigurationException
     */
    public function __construct($config)
    {
        if ( ! extension_loaded("ftp")) {
            throw new ConfigurationException("FTP extension not loaded.");
        }

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

        $this->config = is_string($config) ? $importedConfig[$config] : $config;
        foreach ($this->config as $optionKey => $optionValue) {
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

                default: throw new ConfigurationException("[{$optionKey}] is invalid configuration option.");
            }
        }
    }

}