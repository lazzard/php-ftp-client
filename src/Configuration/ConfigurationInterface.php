<?php

namespace Lazzard\FtpClient\Configuration;

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
     * Get FTP timeout option value.
     *
     * @return int
     */
    public function getTimeout();

    /**
     * @param int $timeout
     *
     * @throws \Lazzard\FtpClient\Configuration\Exception\FtpConfigurationLogicException
     */
    public function setTimeout($timeout);

    /**
     * @return bool
     */
    public function isPassive();

    /**
     * @param bool $passive
     *
     * @throws \Lazzard\FtpClient\Configuration\Exception\FtpConfigurationLogicException
     */
    public function setPassive($passive);

    /**
     * @return bool
     */
    public function isAutoSeek();

    /**
     * @param bool $autoSeek
     *
     * @throws \Lazzard\FtpClient\Configuration\Exception\FtpConfigurationLogicException
     */
    public function setAutoSeek($autoSeek);

    /**
     * @return bool
     */
    public function isUsePassiveAddress();

    /**
     * @param $usePassiveAddress
     *
     * @throws \Lazzard\FtpClient\Configuration\Exception\FtpConfigurationLogicException
     */
    public function setUsePassiveAddress($usePassiveAddress);

    /**
     * @return string
     */
    public function getRoot();

    /**
     * @param $root
     *
     * @throws \Lazzard\FtpClient\Configuration\Exception\FtpConfigurationLogicException
     */
    public function setRoot($root);
}