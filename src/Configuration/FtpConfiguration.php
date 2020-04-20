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
    /** @var int */
    private $timeout;

    /** @var bool */
    private $passive;

    /** @var bool */
    private $autoSeek;

    /** @var bool */
    private $usePassiveAddress;

    /** @var string */
    private $initialDirectory;

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

        $importedConfig = include(__DIR__ . DIRECTORY_SEPARATOR . "Config.php");

        if (is_string($config)) {
            if ( ! key_exists($config, $importedConfig)) {
                throw new ConfigurationException(
                    "Cannot find configuration [{$config}] in the config file.");
            }
        }

        $config = is_string($config) ? $importedConfig[$config] : $config;
        foreach ($config as $optionKey => $optionValue) {
            switch ($optionKey) {

                case "timeout": $this->setTimeout($optionValue); break;

                case "passive": $this->setPassive($optionValue); break;

                case "usePassiveAddress": $this->setUsePassiveAddress($optionValue); break;

                case "autoSeek": $this->setAutoSeek($optionValue); break;

                case "initialDirectory": $this->setInitialDirectory($optionValue); break;

                default: throw new ConfigurationException("[{$optionKey}] invalid configuration setting.");
            }
        }
    }


    /**
     * @inheritDoc
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @inheritDoc
     */
    public function setTimeout($timeout)
    {
        if ( ! is_int($timeout) || $timeout <= 0) {
            throw new ConfigurationException("[{$timeout}] Timeout option value must be an integer and greater than 0.");
        }

        $this->timeout = $timeout;
    }

    /**
     * @inheritDoc
     */
    public function isPassive()
    {
        return $this->passive;
    }

    /**
     * @inheritDoc
     */
    public function setPassive($passive)
    {
        if ( ! is_bool($passive)) {
            throw new ConfigurationException("[{$passive}] must be a boolean value.");
        }

        $this->passive = $passive;
    }

    /**
     * @inheritDoc
     */
    public function isAutoSeek()
    {
        return $this->autoSeek;
    }

    /**
     * @inheritDoc
     */
    public function setAutoSeek($autoSeek)
    {
        if ( ! is_bool($autoSeek)) {
            throw new ConfigurationException("[{$autoSeek}] must be a boolean value.");
        }

        $this->autoSeek = $autoSeek;
    }

    /**
     * @inheritDoc
     */
    public function isUsePassiveAddress()
    {
        return $this->usePassiveAddress;
    }

    /**
     * @inheritDoc
     */
    public function setUsePassiveAddress($usePassiveAddress)
    {
        if ( ! is_bool($usePassiveAddress)) {
            throw new ConfigurationException("[{$usePassiveAddress}] must be a boolean value.");
        }

        $this->usePassiveAddress = $usePassiveAddress;
    }

    /**
     * @inheritDoc
     */
    public function getInitialDirectory()
    {
        return $this->initialDirectory;
    }

    /**
     * @inheritDoc
     */
    public function setInitialDirectory($initialDirectory)
    {
        if ( ! is_string($initialDirectory)) {
            throw new ConfigurationException("[{$initialDirectory}] must be a string value.");
        }

        $this->initialDirectory = $initialDirectory;
    }

}