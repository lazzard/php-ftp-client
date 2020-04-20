<?php


namespace Lazzard\FtpClient\Config;

use Lazzard\FtpClient\Connection\ConnectionInterface;
use Lazzard\FtpClient\Exception\ConfigurationException;
use Lazzard\FtpClient\FtpClient;
use Lazzard\FtpClient\FtpWrapper;

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
     * @param array|null $settings
     *
     * @throws ConfigurationException
     */
    public function __construct($settings = null)
    {
        if (!extension_loaded("ftp")) {
            throw new ConfigurationException("FTP extension not loaded.");
        }

        if ($settings) {
            # Client settings
            foreach ($settings as $optionKey => $optionValue) {
                if (key_exists($optionKey, Config::SETTINGS)) {
                    $setter = "set" . ucfirst($optionKey);
                    $this->$setter($optionValue);
                    continue;
                } else {
                    throw new ConfigurationException("[{$optionKey}] is invalid FTP setting.");
                }
            }
        } else {
            # Default settings
            foreach (Config::SETTINGS as $optionKey => $optionValue) {
                $defaultValue = Config::SETTINGS[$optionKey]['value'];
                $setter = "set" . ucfirst($optionKey);
                $this->$setter($defaultValue);
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