<?php


namespace Lazzard\FtpClient\Configuration;

use Lazzard\FtpClient\Configuration\Exception\FtpConfigurationException;

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
    /** @var bool */
    private $autoSeek = true;

    /**
     * FtpConfiguration constructor.
     *
     * @param array|null $conf
     *
     * @throws \Lazzard\FtpClient\Configuration\Exception\FtpConfigurationException
     */
    public function __construct($conf = null)
    {
        if (is_null($conf) === false)
        {
            foreach ($conf as $option => $value)
            {
                # Get current object vars as an array in insensitive format
                $object_vars_lower_case = array_change_key_case(get_object_vars($this), CASE_LOWER);
                # Check if option is exists
                if (key_exists(strtolower($option), $object_vars_lower_case))
                {
                    # Validate option
                    Contracts::validate([$option => $value]);
                    # Call setter
                    $call_func = "set" . ucfirst($option);
                    $this->$call_func($value);
                } else {
                    throw new FtpConfigurationException("{$option} is invalid FTP configuration option.");
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