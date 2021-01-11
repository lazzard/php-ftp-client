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
 * A simple class to manage an FTP connection.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpConfig
{
    /** @var ConnectionInterface */
    protected $connection;

    /** @var FtpWrapper */
    protected $wrapper;

    /**
     * FtpConfig constructor.
     *
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->wrapper = new FtpWrapper($connection);
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param FtpWrapper $wrapper
     */
    public function setWrapper($wrapper)
    {
        $this->wrapper = $wrapper;
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
            throw new ConfigException($this->wrapper->getFtpErrorMessage()
                ?: "Unable to switch FTP mode.");
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
            throw new ConfigException("[{$value}] Timeout option value must be of type integer and greater than 0.");
        }

        if (!$this->wrapper->set_option(FTP_TIMEOUT_SEC, $value)) {
            throw new ConfigException($this->wrapper->getFtpErrorMessage()
                ?: "Unable to set Timeout runtime option.");
        }

        return true;
    }

    /**
     * Specifies if the IP address returned via the PASV command will be used to open the data channel.
     *
     * @param bool $value
     *
     * @return bool Returns true in success, if not an exception throws.
     *
     * @throws ConfigException
     */
    public function usePassiveAddress($value)
    {
        if (!is_bool($value)) {
            throw new ConfigException("[{$value}] usePassiveAddress option value must be of type boolean.");
        }

        if (!$this->wrapper->set_option(FTP_USEPASVADDRESS, $value)) {
            throw new ConfigException($this->wrapper->getFtpErrorMessage()
                ?: "Unable to set usePassiveAddress runtime option.");
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
            throw new ConfigException("[{$value}] AutoSeek option value must be of type boolean.");
        }

        if (!$this->wrapper->set_option(FTP_AUTOSEEK, $value)) {
            throw new ConfigException($this->wrapper->getFtpErrorMessage()
                ?: "Unable to set AutoSeek runtime option.");
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
        if (!$optionValue = $this->wrapper->get_option(FTP_TIMEOUT_SEC)) {
            throw new ConfigException($this->wrapper->getFtpErrorMessage()
                ?: "Unable to get FTP timeout option value.");
        }

        return $optionValue;
    }

    /**
     * Checks if the autoSeek option enabled or not.
     *
     * @return bool
     */
    public function isAutoSeek()
    {
        return $this->wrapper->get_option(FTP_AUTOSEEK);
    }

    /**
     * Checks if the passive address returned in the PASV response
     * is used by the control channel or not.
     *
     * @return bool
     *
     * @throws ConfigException
     */
    public function isUsePassiveAddress()
    {
        return $this->wrapper->get_option(FTP_USEPASVADDRESS);
    }
}
