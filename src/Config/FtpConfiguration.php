<?php


namespace Lazzard\FtpClient\Config;

use Lazzard\FtpClient\Config\Exception\FtpConfigurationLogicException;
use Lazzard\FtpClient\Config\Exception\FtpConfigurationRuntimeException;

/**
 * Class FtpConfiguration
 *
 * @since 1.0
 * @package Lazzard\FtpClient\FtpConfiguration
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class FtpConfiguration implements ConfigurationInterface
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
    private $root;

    /**
     * FtpConfiguration constructor.
     *
     * @param array|null $settings
     *
     * @throws FtpConfigurationRuntimeException
     * @throws FtpConfigurationLogicException
     */
    public function __construct($settings = null)
    {
        if (!extension_loaded("ftp")) {
            throw new FtpConfigurationRuntimeException("FTP extension not loaded.");
        }

        if ($settings) {
            # Client settings
            foreach ($settings as $optionKey => $optionValue) {
                if (key_exists($optionKey, FtpDefaultSettings::SETTINGS)) {
                    $setter = "set" . ucfirst($optionKey);
                    $this->$setter($optionValue);
                    continue;
                } else {
                    throw new FtpConfigurationLogicException(
                        "[{$optionKey}] is invalid FTP setting."
                    );
                }
            }
        } else {
            # Default settings
            foreach (get_object_vars($this) as $optionKey => $optionValue) {
                $defaultValue = FtpDefaultSettings::SETTINGS[$optionKey]['value'];
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
        if (FtpDefaultSettings::SETTINGS['timeout']['type'] !== gettype($timeout)) {
            throw FtpConfigurationLogicException::InvalidFtpConfigurationOption(
                'timeout',
                FtpDefaultSettings::SETTINGS['timeout']['type']
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
        if (FtpDefaultSettings::SETTINGS['passive']['type'] !== gettype($passive)) {
            throw FtpConfigurationLogicException::InvalidFtpConfigurationOption(
                'passive',
                FtpDefaultSettings::SETTINGS['passive']['type']
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
        if (FtpDefaultSettings::SETTINGS['autoSeek']['type'] !== gettype($autoSeek)) {
            throw FtpConfigurationLogicException::InvalidFtpConfigurationOption(
                'autoSeek',
                FtpDefaultSettings::SETTINGS['autoSeek']['type']
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
        if (FtpDefaultSettings::SETTINGS['usePassiveAddress']['type'] !== gettype($usePassiveAddress)) {
            throw FtpConfigurationLogicException::InvalidFtpConfigurationOption(
                'usePassiveAddress',
                FtpDefaultSettings::SETTINGS['usePassiveAddress']['type']
            );
        }

        $this->usePassiveAddress = $usePassiveAddress;
    }

    /**
     * @inheritDoc
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @inheritDoc
     */
    public function setRoot($root)
    {
        if (FtpDefaultSettings::SETTINGS['root']['type'] !== gettype($root)) {
            throw FtpConfigurationLogicException::InvalidFtpConfigurationOption(
                'root',
                FtpDefaultSettings::SETTINGS['root']['type']
            );
        }

        $this->root = $root;
    }

}