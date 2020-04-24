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
class FtpConfiguration
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
    private $connection;

    /** @var FtpWrapper */
    private $wrapper;

    /** @var array */
    private static $configFile;

    /** @var array */
    private $config;

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
        $this->connection = $connection;

        $this->wrapper = new FtpWrapper($connection);

        self::$configFile = self::$configFile ?: include(__DIR__ . DIRECTORY_SEPARATOR . "Config.php");

        $this->setConfig($config);
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param ConnectionInterface $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param $config
     *
     * @throws ConfigurationException
     */
    public function setConfig($config)
    {
        if (is_string($config)) {
            if ( ! key_exists($config, self::$configFile)) {
                throw new ConfigurationException(
                    "Cannot find configuration [{$config}] in the config file.");
            }
        }

        $this->config = $this->_validateTypeConstraints(
            is_string($config)
                ? self::$configFile[$config]
                : array_merge(self::$configFile["default"], $config)
        );

        $this->setPassive($this->config['passive']);
        $this->setOption(self::TIMEOUT_SEC, $this->config['timeout']);
        $this->setOption(self::AUTOSEEK, $this->config['autoSeek']);
        $this->setOption(self::USEPASVADDRESS, $this->config['usePassiveAddress']);
        $this->wrapper->chdir($this->config['initialDirectory']);

        $this->_setPhpLimit($this->config['phpLimit']);
    }

    /**
     * Gets default configuration.
     *
     * @return array
     */
    public function getDefaultConfiguration()
    {
        return self::$configFile[self::DEFAULT_CONF];
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
    public function setOption($option, $value)
    {
        if ( ! in_array($option, [
            self::TIMEOUT_SEC,
            self::AUTOSEEK,
            self::USEPASVADDRESS
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
    public function getOption($option)
    {
        if ( ! in_array($option, [
            self::TIMEOUT_SEC,
            self::AUTOSEEK,
            self::USEPASVADDRESS
        ], true)) {
            throw new ClientException("[{$option}] is invalid FTP runtime option.");
        }

        if ( ! ($optionValue = $this->wrapper->getOption($option))) {
            throw new ClientException("Cannot get FTP runtime option value.");
        }

        return $optionValue;
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
    private function _validateTypeConstraints($config)
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
                        throw new ConfigurationException("[{$optionKey}] option value must be of type boolean.");
                    }
                    break;

                case "initialDirectory":
                    if ( ! is_string($optionValue)) {
                        throw new ConfigurationException("[{$optionKey}] option value must be of type string.");
                    }
                    break;

                case "phpLimit":

                    foreach ($config['phpLimit'] as $limitKey => $limitValue) {

                        switch ($limitKey) {

                            case "maxExecutionTime":

                                if ( ! is_int($limitValue) &&
                                    $limitValue !== NOT_CHANGE &&
                                    $limitValue !== UNLIMITED) {
                                    throw new ConfigurationException("[{$limitKey}] value must be of type integer.");
                                }
                                break;

                            case "ignoreUserAbort":

                                if ( ! is_bool($limitValue) &&
                                    $limitValue !== NOT_CHANGE) {
                                    throw new ConfigurationException("[{$limitKey}] value must be of boolean type.");
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
    private function _setPhpLimit($config)
    {
        if ($config['maxExecutionTime'] !== NOT_CHANGE ) {
            if ( ! set_time_limit($config['maxExecutionTime'] === UNLIMITED ? 0 : $config['maxExecutionTime'])) {
                throw new ConfigurationException("Failed to set max_execution_time directive value.");
            }
        }

        if ($config['ignoreUserAbort'] !== NOT_CHANGE) {
            ignore_user_abort($config['ignoreUserAbort']);
            if ((bool)ini_get('ignore_user_abort') !== $config['ignoreUserAbort']) {
                throw new ConfigurationException("Unable to set ignore_user_abort directive value.");
            }
        }
    }
}