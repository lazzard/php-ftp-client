<?php


namespace Lazzard\FtpClient\Configuration;

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
    private $timeout = 90;
    /** @var bool */
    private $passive = false;

    /**
     * FtpConfiguration constructor.
     *
     * @param array|null $conf
     */
    public function __construct($conf = null)
    {
        if (is_null($conf) === false) {
            $this->timeout = $conf['timeout'];
            $this->passive = $conf['passive'];
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
    public function isPassive()
    {
        return $this->passive;
    }

}