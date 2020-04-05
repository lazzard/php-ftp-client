<?php


namespace Lazzard\FtpClient\Configuration;

use Lazzard\FtpClient\Configuration\Exception\FtpOptionException;
use Lazzard\FtpClient\Configuration\Utilities\FtpOptionsContracts;

/**
 * Class FtpOptions Abstract FTP configuration functions.
 *
 * @since 1.0
 * @package Lazzard\FtpClient\FtpConfiguration
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
abstract class FtpOptions
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
     * Validate FTP options.
     *
     * @param array|null $options
     *
     * @throws \Lazzard\FtpClient\Configuration\Exception\ExtensionException
     */
    public function __construct($options = null)
    {
        if (is_null($options) === false) {
            foreach ($options as $option => $value)
            {
                # Get current object vars as an array in insensitive format
                $object_vars_lower_case = array_change_key_case(get_object_vars($this), CASE_LOWER);
                # Check if option is exists
                $option_lower_case = strtolower($option);
                if (key_exists($option_lower_case, $object_vars_lower_case)) {
                    # Validate option
                    if (FtpOptionsContracts::validate([$option_lower_case => $value]) === true) {
                        # Call setter
                        $call_func = "set" . ucfirst($option_lower_case);
                        $this->$call_func($value);
                    }
                } else {
                    # Invalid configuration option
                    throw FtpOptionException::invalidConfigurationOption($option);
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