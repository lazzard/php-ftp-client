<?php


namespace Lazzard\FtpClient\Config;

use Lazzard\FtpClient\Exception\ConfigException;

/**
 * Class Configuration
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Configuration
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class Configuration implements Configurable
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
     * Configuration constructor.
     *
     * @param array|null $settings
     *
     * @throws ConfigException
     */
    public function __construct($settings = null)
    {
        if (!extension_loaded("ftp")) {
            throw new ConfigException("FTP extension not loaded.");
        }

        if ($settings) {
            # Client settings
            foreach ($settings as $optionKey => $optionValue) {
                if (key_exists($optionKey, Config::SETTINGS)) {
                    $setter = "set" . ucfirst($optionKey);
                    $this->$setter($optionValue);
                    continue;
                } else {
                    throw new ConfigException("[{$optionKey}] is invalid FTP setting.");
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
        if (Config::SETTINGS['timeout']['type'] !== gettype($timeout)) {
            throw ConfigException::InvalidFtpConfigurationSetting(
                'timeout',
                Config::SETTINGS['timeout']['type']
            );
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
        if (Config::SETTINGS['passive']['type'] !== gettype($passive)) {
            throw ConfigException::InvalidFtpConfigurationSetting(
                'passive',
                Config::SETTINGS['passive']['type']
            );
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
        if (Config::SETTINGS['autoSeek']['type'] !== gettype($autoSeek)) {
            throw ConfigException::InvalidFtpConfigurationSetting(
                'autoSeek',
                Config::SETTINGS['autoSeek']['type']
            );
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
        if (Config::SETTINGS['usePassiveAddress']['type'] !== gettype($usePassiveAddress)) {
            throw ConfigException::InvalidFtpConfigurationSetting(
                'usePassiveAddress',
                Config::SETTINGS['usePassiveAddress']['type']
            );
        }

        $this->usePassiveAddress = $usePassiveAddress;
    }

    /**
     * @inheritDoc
     */
    public function getinitialDirectory()
    {
        return $this->initialDirectory;
    }

    /**
     * @inheritDoc
     */
    public function setinitialDirectory($initialDirectory)
    {
        if (Config::SETTINGS['initialDirectory']['type'] !== gettype($initialDirectory)) {
            throw ConfigException::InvalidFtpConfigurationSetting(
                'initialDirectory',
                Config::SETTINGS['initialDirectory']['type']
            );
        }

        $this->initialDirectory = $initialDirectory;
    }

}