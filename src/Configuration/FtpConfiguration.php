<?php

namespace Lazzard\FtpClient\Configuration;

use Lazzard\FtpClient\Connection\ConnectionInterface;
use Lazzard\FtpClient\Exception\ClientException;
use Lazzard\FtpClient\Exception\ConfigurationException;
use Lazzard\FtpClient\FtpWrapper;

/**
 * Class FtpConfiguration
 *
 * @since 1.0
 * @package Lazzard\FtpClient\FtpConfiguration
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class FtpConfiguration extends FileConfiguration
{
    /**
     * Predefined configurations by the ftp client.
     */
    const DEFAULT_CONF = 'default';

    /**
     * FtpWrapper constants.
     */
    const USEPASVADDRESS = FtpWrapper::USEPASVADDRESS;
    const TIMEOUT_SEC    = FtpWrapper::TIMEOUT_SEC;
    const AUTOSEEK       = FtpWrapper::AUTOSEEK;

    /** @var ConnectionInterface */
    protected $connection;

    /** @var FtpWrapper */
    protected $wrapper;

    /**
     * FtpConfiguration constructor.
     *
     * @param ConnectionInterface $connection
     * @param array|string        $config
     *
     * @throws ConfigurationException
     */
    public function __construct(ConnectionInterface $connection, $config)
    {
        parent::__construct();

        $this->connection = $connection;
        $this->wrapper = new FtpWrapper($connection);

        $this->setConfig($config);
        $this->_validateConfiguration();
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
        $this->setRuntimeOption(FtpWrapper::TIMEOUT_SEC, $this->config['timeout']);
        $this->setRuntimeOption(FtpWrapper::AUTOSEEK, $this->config['autoSeek']);
        $this->setRuntimeOption(FtpWrapper::USEPASVADDRESS, $this->config['usePassiveAddress']);
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
     * Set FTP runtime options.
     *
     * @param $option
     * @param $value
     *
     * @return bool
     *
     * @throws ConfigurationException
     */
    public function setRuntimeOption($option, $value)
    {
        if ( ! in_array($option, [
            self::TIMEOUT_SEC,
            self::AUTOSEEK,
            self::USEPASVADDRESS
        ], true)) {
            throw new ConfigurationException("[{$option}] is invalid FTP runtime option.");
        }

        if ( ! $this->wrapper->setOption($option, $value)) {
            throw new ConfigurationException(
                ConfigurationException::getFtpServerError()
                ?: "Unable to set FTP option."
            );
        }

        return true;
    }


    /**
     * Gets an FTP runtime option value.
     *
     * @param string $option
     *
     * @return mixed
     *
     * @throws ConfigurationException
     */
    public function getRuntimeOption($option)
    {
        if ( ! in_array($option, [
            self::TIMEOUT_SEC,
            self::AUTOSEEK,
            self::USEPASVADDRESS
        ], true)) {
            throw new ConfigurationException("[{$option}] is invalid FTP runtime option.");
        }

        if ( ! ($optionValue = $this->wrapper->getOption($option))) {
            throw new ConfigurationException(
                ConfigurationException::getFtpServerError()
                ?: "Cannot get FTP runtime option value."
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
    protected function init()
    {
        $this->config = $this->getConfigByName(self::DEFAULT_CONF);
    }
    
    /**
     * @inheritDoc
     */
    protected function _validateConfiguration()
    {
        /** @var mixed $optionValue */
        foreach ($this->config as $optionKey => $optionValue) switch ($optionKey) {

            case "timeout":
                if ( ! is_int($optionValue) || $optionValue <= 0) {
                    throw new ConfigurationException("[{$optionValue}] Timeout option value must be an integer and greater than 0.");
                }
                break;

            case "passive": case "usePassiveAddress": case "autoSeek":
                if ( ! is_bool($optionValue)) {
                    throw new ConfigurationException("[{$optionKey}] option value must be of type boolean.");
                }
                break;

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