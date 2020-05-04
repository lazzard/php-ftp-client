<?php

namespace Lazzard\FtpClient\Configuration;

use Lazzard\FtpClient\Exception\ConfigurationException;

/**
 * Change the ini directives according to client configurations   
 *
 * @since 1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class PhpIniConfig extends FileConfiguration
{
    /**
     * Default configuration name.
     */
    const CONFIG_NAME = "phpLimit";

    /**
     * PhpIniConfig constructor.
     *
     * @param array|null $config
     *
     * @throws ConfigurationException
     */
    public function __construct($config = null)
    {
        parent::__construct();

        $this->setConfig($config);
        $this->validateConfiguration();
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @inheritdoc
     */
    public function setConfig($config)
    {
        $this->config = $config ? $this->merge($config) : $this->getConfigByName(self::CONFIG_NAME);
    }

    /**
     * Sets the php limit configuration options specified in the config file.
     * 
     * @throws ConfigurationException
     */
    public function apply()
    {
        if (($value = $this->config['maxExecutionTime']) !== NOT_CHANGE ) {
            if ( ! set_time_limit($value === UNLIMITED ? 0 : $value)) {
                throw new ConfigurationException("Failed to set max_execution_time value to [{$value}].");
            }
        }

        if (($value = $this->config['ignoreUserAbort']) !== NOT_CHANGE) {
            ignore_user_abort($value);

            if ((bool)ini_get('ignore_user_abort') !== $value) {
                throw new ConfigurationException("Unable to set ignore_user_abort value to [{$value}].");
            }
        }

        if (($value = $this->config['memoryLimit']) !== NOT_CHANGE) {
            ini_set('memory_limit', sprintf('%sM', $value));

            if ((int)ini_get('memory_limit') !== $value) {
                throw new ConfigurationException("Failed to set memory_limit value to [{$value}M].");
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function merge($config)
    {
        return array_merge($this->getConfigByName(self::CONFIG_NAME), $config);
    }

    /**
     * @inheritDoc
     */
    protected function validateConfiguration()
    {
        /** @var mixed $optionValue */
        foreach ($this->config as $optionKey => $optionValue) switch ($optionKey) {

            case "maxExecutionTime": case "memoryLimit":
                if ( ! is_int($optionValue) && ! in_array($optionValue, [NOT_CHANGE, UNLIMITED]) ) {
                    throw new ConfigurationException("[{$optionKey}] option value must be of type integer.");
                }
                break;

            case "ignoreUserAbort":
                if ( ! is_bool($optionValue) && ! in_array($optionValue, [NOT_CHANGE, UNLIMITED])) {
                    throw new ConfigurationException("[{$optionKey}] option value must be of type boolean.");
                }
                break;

            default: throw new ConfigurationException("[{$optionKey}] is invalid configuration option.");
        }

        return true;
    }

}