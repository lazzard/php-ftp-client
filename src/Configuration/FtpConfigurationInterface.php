<?php


namespace Lazzard\FtpClient\Configuration;

/**
 * interface FtpConfigurationInterface
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Configuration
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
interface FtpConfigurationInterface
{

    /**
     * Get configuration timeout.
     *
     * @return int
     */
    public function getTimeout();

    /**
     * If passive mode.
     *
     * @return bool
     */
    public function isPassive();
}