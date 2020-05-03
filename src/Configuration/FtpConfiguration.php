<?php

namespace Lazzard\FtpClient\Configuration;

use Lazzard\FtpClient\Connection\ConnectionInterface;
use Lazzard\FtpClient\Exception\ClientException;
use Lazzard\FtpClient\Exception\ConfigurationException;
use Lazzard\FtpClient\FtpWrapper;

/**
 * Setting config file configuration options 
 *
 * @since 1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpConfiguration extends FileConfiguration
{
    /**
     * Predefined configurations by the ftp client.
     */
    const DEFAULT_CONF = 'default';

    /** @var ConnectionInterface */
    protected $connection;

    /** @var FtpWrapper */
    protected $wrapper;

    /**
     * FtpConfiguration constructor.
     *
     * @param ConnectionInterface $connection
     * @param array|string        $config[optional]
     *
     * @throws ConfigurationException
     */
    public function __construct(ConnectionInterface $connection, $config = null)
    {
        parent::__construct();

        $this->connection = $connection;
        $this->wrapper = new FtpWrapper($connection);
        
        if ($config) {
            $this->setConfig($config);
            $this->validateConfiguration();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ConfigurationException
     */
    public function setConfig($config)
    {
        if (is_string($config)) {
            if ( ! $this->getConfigByName($config) || $config === PhpIniConfig::CONFIG_NAME) {
                throw new ConfigurationException(
                    "Cannot find configuration [{$config}] in the config file."
                );
            }
            $this->config = $this->getConfigByName(self::DEFAULT_CONF);
        } else {
            $this->config = $this->merge($config);
        }
    }

    /**
     * Sets the FTP configuration options retrieved from the config file.
     *
     * @throws ClientException
     */
    public function apply()
    {
        $this->setPassive($this->config['passive']);
        $this->setTimeout($this->config['timeout']);
        $this->setAutoSeek($this->config['autoSeek']);
        $this->usePassiveAddress($this->config['usePassiveAddress']);
        $this->wrapper->chdir($this->config['initialDirectory']);
    }

    /**
     * Turn the passive mode on or off.
     *
     * Notice that the active mode is the default mode.
     *
     * @param $bool
     *
     * @return bool
     *
     * @throws ConfigurationException
     */
    public function setPassive($bool)
    {
        if ( ! $this->wrapper->pasv($bool)) {
            throw new ConfigurationException(
                ConfigurationException::getFtpServerError()
                ?: "Unable to switch FTP mode."
            );
        }

        return true;
    }

    /**
     * Sets the timeout in seconds for all FTP network operations.
     *
     * @param int $value
     *
     * @return bool
     *
     * @throws ConfigurationException
     */
    public function setTimeout($value)
    {
        if ( ! is_int($value) || $value < 0) {
            throw new ConfigurationException(
                "[{$value}] Timeout option value must be of type integer and greater than 0."
            );
        }

        if ( ! $this->wrapper->setOption(FtpWrapper::TIMEOUT_SEC, $value)) {
            throw new ConfigurationException(ConfigurationException::getFtpServerError()
                ?: "Unable to set Timeout runtime option."
            );
        }

        return true;
    }

    /**
     * Sets the autoSeek option on/off.
     *
     * @param bool $value
     *
     * @return bool
     *
     * @throws ConfigurationException
     */
    public function setAutoSeek($value)
    {
        if ( ! is_bool($value)) {
            throw new ConfigurationException(
                "[{$value}] AutoSeek option value must be of type boolean."
            );
        }

        if ( ! $this->wrapper->setOption(FtpWrapper::AUTOSEEK, $value)) {
            throw new ConfigurationException(ConfigurationException::getFtpServerError()
                ?: "Unable to set AutoSeek runtime option."
            );
        }

        return true;
    }

    /**
     * Specifies if the IP address returned via the PASV command
     * will be used to open the control channel.
     *
     * @param bool $value
     *
     * @return bool
     *
     * @throws ConfigurationException
     */
    public function usePassiveAddress($value)
    {
        if ( ! is_bool($value)) {
            throw new ConfigurationException(
                "[{$value}] usePassiveAddress option value must be of type boolean."
            );
        }

        if ( ! $this->wrapper->setOption(FtpWrapper::USEPASVADDRESS, $value)) {
            throw new ConfigurationException(ConfigurationException::getFtpServerError()
                ?: "Unable to set usePassiveAddress runtime option."
            );
        }

        return true;
    }
    
    /**
     * Gets the timeout option value.
     * 
     * @return int
     * 
     * @throws ConfigurationException
     */
    public function getTimeout()
    {
        if ( ! ($optionValue = $this->wrapper->getOption(FtpWrapper::TIMEOUT_SEC))) {
            throw new ConfigurationException(ConfigurationException::getFtpServerError()
                ?: "Unable to get FTP timeout option value."
            );
        }

        return $optionValue;   
    }
    
    /**
     * Checks if the autoSeek option enabled or not.
     * 
     * @return bool
     * 
     * @throws ConfigurationException
     */
    public function isAutoSeek()
    {
        if ( ! ($optionValue = $this->wrapper->getOption(FtpWrapper::AUTOSEEK))) {
            throw new ConfigurationException(ConfigurationException::getFtpServerError()
                ?: "Unable to get FTP timeout option value."
            );
        }

        return $optionValue;   
    }

    /**
     * Checks if the passive address returned in the PASV response
     * is used by the control channel or not. 
     *
     * @return bool
     * 
     * @throws ConfigurationException
     */
    public function isUsePassiveAddress()
    {
        if ( ! ($optionValue = $this->wrapper->getOption(FtpWrapper::AUTOSEEK))) {
            throw new ConfigurationException(ConfigurationException::getFtpServerError()
                ?: "Unable to get FTP timeout option value."
            );
        }

        return $optionValue;  
    }


    /**
     * @inheritDoc
     */
    protected function merge($config)
    {
        return array_merge($this->getConfigByName(self::DEFAULT_CONF), $config);
    }

    /**
     * @inheritDoc
     */
    protected function validateConfiguration()
    {
        /** @var mixed $optionValue */
        foreach ($this->config as $optionKey => $optionValue) switch ($optionKey) {

            case "timeout": continue; case "passive": continue; 
            case "usePassiveAddress": continue; case "autoSeek": continue;

            case "initialDirectory":
                if ( ! is_string($optionValue)) {
                    throw new ConfigurationException("[{$optionKey}] option value must be of type string.");
                }
                break;

            default: throw new ConfigurationException("[{$optionKey}] is invalid configuration option.");
        }

        return true;
    }
}