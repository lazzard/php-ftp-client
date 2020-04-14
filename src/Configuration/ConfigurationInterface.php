<?php

namespace Lazzard\FtpClient\Configuration;

use Lazzard\FtpClient\Configuration\Exception\FtpConfigurationLogicException;

/**
 * Interface ConfigurationInterface
 *
 * @since 1.0
 * @package Lazzard\FtpClient\FtpConfiguration
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
interface ConfigurationInterface
{
    /**
     * Gets FTP timeout value of an FTP configuration instance.
     *
     * @return int
     */
    public function getTimeout();

    /**
     * Sets FTP timeout value for an FTP configuration instance,
     * Must be an integer and greater than 0.
     *
     * @param int $timeout
     *
     * @throws FtpConfigurationLogicException
     */
    public function setTimeout($timeout);

    /**
     * @return bool
     */
    public function isPassive();

    /**
     * @param bool $passive
     *
     * @throws FtpConfigurationLogicException
     */
    public function setPassive($passive);

    /**
     * @return bool
     */
    public function isAutoSeek();

    /**
     * @param bool $autoSeek
     *
     * @throws FtpConfigurationLogicException
     */
    public function setAutoSeek($autoSeek);

    /**
     * Gets true if passive mode of an FTP configuration instance is activated,
     * Otherwise return false.
     *
     * @return bool
     */
    public function isUsePassiveAddress();

    /**
     * Sets passive/active mode for an FTP configuration instance.
     *
     * @param $usePassiveAddress
     *
     * @throws FtpConfigurationLogicException
     */
    public function setUsePassiveAddress($usePassiveAddress);

    /**
     * Gets the initial directory of an FTP configuration instance.
     *
     * @return string
     */
    public function getRoot();

    /**
     * Sets the initial directory of an FTP configuration instance.
     *
     * @param $root
     *
     * @throws FtpConfigurationLogicException
     */
    public function setRoot($root);
}