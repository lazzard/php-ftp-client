<?php


namespace Lazzard\FtpClient\Configuration;

use Lazzard\FtpClient\Configuration\Exception\ConfigurationException;
use Lazzard\FtpClient\Configuration\Utilities\FtpOptionValidator;

/**
 * Class FtpConfiguration
 *
 * @since 1.0
 * @package Lazzard\FtpClient
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class FtpConfiguration
{
    /** @var int */
    private $timeout = 90;
    /** @var bool */
    private $passive = false;
    /** @var bool */
    private $autoSeek = true;

    /**
     * FtpOptions constructor.
     *
     * @param array|null $options
     *
     * @throws \Lazzard\FtpClient\Configuration\Exception\ConfigurationException
     * @throws \ReflectionException
     */
    public function __construct($options = null)
    {
        if (extension_loaded("ftp") === false) {
            throw new ConfigurationException("FTP extension not loaded.");
        }

        if (is_null($options) === false) {

            foreach ($options as $optionKey => $optionValue) {

                if (key_exists($optionKey, get_object_vars($this))) {

                    if (FtpOptionValidator::validate([$optionKey => $optionValue]) !== false) {

                        $setter = "set" . ucfirst($optionKey);
                        $this->$setter($optionValue);

                    } else {
                        $prop = new \ReflectionProperty(self::class, $optionKey);
                        $type = explode(' ', $prop->getDocComment())[2];

                        throw new ConfigurationException(sprintf(
                            "%s option accept value of type %s",
                            $optionKey,
                            $type
                        ));
                    }

                } else {
                    throw new ConfigurationException("{$optionKey} is invalid FTP configuration option.");
                }
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
     * @param bool $passive
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
     * @param bool $autoSeek
     */
    public function setAutoSeek($autoSeek)
    {
        $this->autoSeek = $autoSeek;
    }

}