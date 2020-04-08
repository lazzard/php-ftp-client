<?php


namespace Lazzard\FtpClient\Configuration;

use Lazzard\FtpClient\Configuration\Exception\FtpConfigurationLogicException;
use Lazzard\FtpClient\Configuration\Exception\FtpConfigurationRuntimeException;
use Lazzard\FtpClient\Exception\FtpClientLogicException;

/**
 * Class FtpConfiguration
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Configuration
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class FtpConfiguration implements FtpConfigurationInterface
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
     * FtpSettings constructor.
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

                    throw new FtpClientLogicException(sprintf(
                        "%s option accept value of type %s",
                        $optionKey,
                        FtpSettings::SETTINGS[$optionKey]['type']
                    ));
                }

                throw new FtpConfigurationLogicException("{$optionKey} is invalid FTP configuration option.");
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
     * @param int $timeout
     */
    private function setTimeout($timeout)
    {
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
     * @param bool $passive
     */
    private function setPassive($passive)
    {
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
     * @param bool $autoSeek
     */
    private function setAutoSeek($autoSeek)
    {
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
     * @param bool $usePassiveAddress
     */
    private function setUsePassiveAddress($usePassiveAddress)
    {
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
     * @param string $root
     */
    private function setRoot($root)
    {
        $this->root = $root;
    }

}