<?php

/**
 * This file is part of the Lazzard/php-ftp-client package.
 *
 * (c) El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lazzard\FtpClient\Config;

use Lazzard\FtpClient\Connection\ConnectionInterface;
use Lazzard\FtpClient\Exception\ConfigException;
use Lazzard\FtpClient\FtpWrapper;

/**
 * Simple class to manage an FTP connection.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
final class FtpConfig
{
    /** @var FtpWrapper */
    private $wrapper;

    /** @var array */
    private $config = [
        'passive'           => false,
        'timeout'           => 90,
        'autoSeek'          => true,
        'initialDirectory'  => '/'
    ];

    /**
     * FtpConfig constructor.
     *
     * @param ConnectionInterface $connection
     * @param array|null          $config [optional]
     */
    public function __construct(ConnectionInterface $connection, $config = null)
    {
        if ($config) {
            $this->config = array_merge($this->config, $config);
        }

        $this->wrapper = new FtpWrapper($connection);
    }

    /**
     * Gets configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Sets client's configuration.
     *
     * @throws ConfigException
     */
    public function apply()
    {
        $this->setPassive($this->config['passive']);
        $this->setTimeout($this->config['timeout']);
        $this->setAutoSeek($this->config['autoSeek']);
        $this->wrapper->chdir($this->config['initialDirectory']);
    }

    /**
     * Turn the passive mode on or off.
     *
     * @param bool $value
     *
     * @return bool Returns true in success, if not an exception throws.
     *
     * @throws ConfigException
     */
    public function setPassive($value)
    {
        if (!$this->wrapper->pasv($value)) {
            throw new ConfigException(ConfigException::getFtpServerError() ?: "Unable to switch FTP mode.");
        }

        return true;
    }

    /**
     * Sets the timeout in seconds for all FTP network operations.
     *
     * @param int $value The timeout value in seconds.
     *
     * @return bool Returns true in success, if not an exception throws.
     *
     * @throws ConfigException
     */
    public function setTimeout($value)
    {
        if (!is_int($value) || $value < 0) {
            throw new ConfigException(
                "[{$value}] Timeout option value must be of type integer and greater than 0."
            );
        }

        if (!$this->wrapper->setOption(FtpWrapper::TIMEOUT_SEC, $value)) {
            throw new ConfigException(ConfigException::getFtpServerError() ?:
                "Unable to set Timeout runtime option.");
        }

        return true;
    }

    /**
     * Sets the autoSeek option on/off.
     *
     * @param bool $value
     *
     * @return bool Returns true in success, if not an exception throws.
     *
     * @throws ConfigException
     */
    public function setAutoSeek($value)
    {
        if (!is_bool($value)) {
            throw new ConfigException(
                "[{$value}] AutoSeek option value must be of type boolean."
            );
        }

        if (!$this->wrapper->setOption(FtpWrapper::AUTOSEEK, $value)) {
            throw new ConfigException(ConfigException::getFtpServerError() ?: "Unable to set AutoSeek runtime option.");
        }

        return true;
    }

    /**
     * Gets the timeout option value.
     *
     * @return int
     *
     * @throws ConfigException
     */
    public function getTimeout()
    {
        if (!($optionValue = $this->wrapper->getOption(FtpWrapper::TIMEOUT_SEC))) {
            throw new ConfigException(ConfigException::getFtpServerError()
                ?: "Unable to get FTP timeout option value.");
        }

        return $optionValue;
    }

    /**
     * Checks if the autoSeek option enabled or not.
     *
     * @return bool
     *
     * @throws ConfigException
     */
    public function isAutoSeek()
    {
        if (!($optionValue = $this->wrapper->getOption(FtpWrapper::AUTOSEEK))) {
            throw new ConfigException(ConfigException::getFtpServerError() ?:
                "Unable to get FTP timeout option value.");
        }

        return $optionValue;
    }
}
