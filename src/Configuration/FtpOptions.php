<?php


namespace Lazzard\FtpClient\Configuration;

use Lazzard\FtpClient\Configuration\Exception\OptionException;
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
            # Looping over client giving options
            foreach ($options as $option => $value) {

                # Get current object vars as an array in insensitive format
                $objVarsToLower = array_change_key_case(get_object_vars($this), CASE_LOWER);

                # Lower case option
                $optionToLower = strtolower($option);

                # Check if option is exists
                if (key_exists($optionToLower, $objVarsToLower)) {

                    # Validate option
                    if (FtpOptionsContracts::validate([$optionToLower => $value]) === true) {
                        # Call the appropriate setter
                        $call_func = "set" . ucfirst($optionToLower);
                        $this->$call_func($value);
                    }

                } else {
                    # Invalid configuration option
                    throw new OptionException("{$option} is invalid FTP configuration option.");
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