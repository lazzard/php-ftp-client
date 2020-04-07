<?php

namespace Lazzard\FtpClient\Configuration;

/**
 * Interface FtpConfigurationInterface
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Configuration
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
interface FtpConfigurationInterface
{

    /**
     * Get FTP timeout option value.
     *
     * @return int
     */
    public function getTimeout();

    /**
     * Set FTP timeout option value.
     *
     * @param int
     */
    public function setTimeout($timeout);

    /**
     * @return bool
     */
    public function isPassive();

    /**
     * @param bool $passive
     */
    public function setPassive($passive);

    /**
     * @return bool
     */
    public function isAutoSeek();

    /**
     * @param bool $autoSeek
     */
    public function setAutoSeek($autoSeek);

    /**
     * @return bool
     */
    public function isUsePassiveAddress();

    /**
     * @param bool $usePassiveAddress
     */
    public function setUsePassiveAddress($usePassiveAddress);

    /**
     * @return string
     */
    public function getRoot();

    /**
     * @param string $root
     */
    public function setRoot($root);


}