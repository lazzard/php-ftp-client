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

    /** @var array */
    protected $config;

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
        $this->wrapper    = new FtpWrapper($connection);

        if (is_string($config)) {
            if ( ! key_exists($config, self::$configFile)) {
                throw new ConfigurationException(
                    "Cannot find configuration [{$config}] in the config file.");
            }
        }

        $this->setConfig($config);
        $this->_validateTypeConstraints();
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
        $this->config =
            is_string($config)
                ? self::$configFile[$config]
                : array_merge(self::$configFile[self::DEFAULT_CONF], $config);
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
     * @throws ClientException
     */
    public function setPassive($bool)
    {
        if ( ! $this->wrapper->pasv($bool)) {
            throw new ClientException("Unable to switch FTP mode.");
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
     * @throws ClientException
     */
    public function setRuntimeOption($option, $value)
    {
        if ( ! in_array($option, [
            FtpWrapper::TIMEOUT_SEC,
            FtpWrapper::AUTOSEEK,
            FtpWrapper::USEPASVADDRESS
        ], true)) {
            throw new ClientException("[{$option}] is invalid FTP runtime option.");
        }

        if ( ! $this->wrapper->setOption($option, $value)) {
            throw new ClientException("Unable to set FTP option.");
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
     * @throws ClientException
     */
    public function getRuntimeOption($option)
    {
        if ( ! in_array($option, [
            FtpWrapper::TIMEOUT_SEC,
            FtpWrapper::AUTOSEEK,
            FtpWrapper::USEPASVADDRESS
        ], true)) {
            throw new ClientException("[{$option}] is invalid FTP runtime option.");
        }

        if ( ! ($optionValue = $this->wrapper->getOption($option))) {
            throw new ClientException("Cannot get FTP runtime option value.");
        }

        return $optionValue;
    }

    /**
     * Validate the option values types constraints in the config file.
     *
     * @return array
     *
     * @throws ConfigurationException
     */
    protected function _validateTypeConstraints()
    {
        foreach ($this->config as $optionKey => $optionValue) {
            switch ($optionKey) {

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
        }

        return $this->config;
    }

}