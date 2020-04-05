<?php


namespace Lazzard\FtpClient\Configuration;

/**
 * Abstract FTP configuration functions.
 *
 * @since 1.0
 * @package Lazzard\FtpClient\FtpConfiguration
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
abstract class FtpConfigurationOptions
{
    /** @var int */
    protected $timeout = 90;
    /** @var bool */
    protected $passive = false;
    /** @var bool */
    protected $autoSeek = true;

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
    protected function setTimeout($timeout)
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
    protected function setPassive($passive)
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
    protected function setAutoSeek($autoSeek)
    {
        $this->autoSeek = $autoSeek;
    }
}