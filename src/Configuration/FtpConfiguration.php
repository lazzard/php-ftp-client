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
        if (extension_loaded("ftp") === false) {
            throw new FtpConfigurationRuntimeException("FTP extension not loaded.");
        }

        if (is_null($options) === false) {

            foreach ($options as $optionKey => $optionValue) {

                if (key_exists($optionKey, FtpSettings::settings)) {

                    if (FtpSettings::settings[$optionKey]['type'] === gettype($optionValue)) {
                        $setter = "set" . ucfirst($optionKey);
                        $this->$setter($optionValue);
                        continue;
                    }

                    throw new FtpClientLogicException(sprintf(
                        "%s option accept value of type %s",
                        $optionKey,
                        FtpSettings::settings[$optionKey]['type']
                    ));
                }

                throw new FtpConfigurationLogicException("{$optionKey} is invalid FTP configuration option.");
            }
        } else {

            foreach (get_object_vars($this) as $optionKey => $optionValue) {
                $defaultValue = FtpSettings::settings[$optionKey]['value'];
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
        $this->root = $root;
    }


}