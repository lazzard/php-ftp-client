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
     * @return bool
     */
    public function isPassive();

    /**
     * @return bool
     */
    public function isAutoSeek();

    /**
     * @return bool
     */
    public function isUsePassiveAddress();

    /**
     * @return string
     */
    public function getRoot();

}