<?php


namespace Lazzard\FtpClient\Configuration;

use Lazzard\FtpClient\Configuration\Exception\FtpConfigurationLogicException;
use Lazzard\FtpClient\Configuration\Exception\FtpConfigurationRuntimeException;

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
     * @param array|null $options
     *
     * @throws \Lazzard\FtpClient\Configuration\Exception\FtpConfigurationRuntimeException
     * @throws \Lazzard\FtpClient\Configuration\Exception\FtpConfigurationLogicException
     */
    public function __construct($options = null)
    {
        if (extension_loaded("ftp") !== true) {
            throw new FtpConfigurationRuntimeException("FTP extension not loaded.");
        }

        if (is_null($options) !== true) {

            foreach ($options as $optionKey => $optionValue) {

                if (key_exists($optionKey, FtpSettings::SETTINGS)) {
                    if (FtpSettings::SETTINGS[$optionKey]['type'] === gettype($optionValue)) {
                        $setter = "set" . ucfirst($optionKey);
                        $this->$setter($optionValue);
                        continue;
                    }
                }

                throw FtpConfigurationLogicException::InvalidFtpConfigurationOption($optionKey,
                    FtpSettings::SETTINGS[$optionKey]['type']);
            }
        } else {

            foreach (get_object_vars($this) as $optionKey => $optionValue) {
                $defaultValue = FtpSettings::SETTINGS[$optionKey]['value'];
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
        if (FtpSettings::SETTINGS['timeout']['type'] === gettype($timeout)) {
            $this->timeout = $timeout;
        } else {
            throw FtpConfigurationLogicException::InvalidFtpConfigurationOption(
                'timeout',
                FtpSettings::SETTINGS['timeout']['type']
            );
        }
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
        if (FtpSettings::SETTINGS['passive']['type'] === gettype($passive)) {
            $this->passive = $passive;
        } else {
            throw FtpConfigurationLogicException::InvalidFtpConfigurationOption(
                'passive',
                FtpSettings::SETTINGS['passive']['type']
            );
        }
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
        if (FtpSettings::SETTINGS['autoSeek']['type'] === gettype($autoSeek)) {
            $this->autoSeek = $autoSeek;
        } else {
            throw FtpConfigurationLogicException::InvalidFtpConfigurationOption(
                'autoSeek',
                FtpSettings::SETTINGS['autoSeek']['type']
            );
        }
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
        if (FtpSettings::SETTINGS['usePassiveAddress']['type'] === gettype($usePassiveAddress)) {
            $this->usePassiveAddress = $usePassiveAddress;
        } else {
            throw FtpConfigurationLogicException::InvalidFtpConfigurationOption(
                'usePassiveAddress',
                FtpSettings::SETTINGS['usePassiveAddress']['type']
            );
        }
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
        if (FtpSettings::SETTINGS['root']['type'] === gettype($root)) {
            $this->root = $root;
        } else {
            throw FtpConfigurationLogicException::InvalidFtpConfigurationOption(
                'root',
                FtpSettings::SETTINGS['root']['type']
            );
        }
    }

}