<?php declare(strict_types=1);

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
 * Manage an FTP connection instance.
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
        $this->connection = $connection;
        $this->wrapper    = new FtpWrapper($connection);
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection() : ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * @param ConnectionInterface $connection
     *
     * @since 1.5.3
     *
     * @return void
     */
    public function setConnection(ConnectionInterface $connection) : void
    {
        $this->connection = $connection;
    }

    /**
     * @param FtpWrapper $wrapper
     *
     * @return void
     */
    public function setWrapper(FtpWrapper $wrapper) : void
    {
        $this->wrapper = $wrapper;
    }

    /**
     * @return FtpWrapper
     *
     * @since 1.5.3
     */
    public function getWrapper() : FtpWrapper
    {
        return $this->wrapper;
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
    public function setPassive(bool $value) : bool
    {
        if (!$this->wrapper->pasv($value)) {
            throw new ConfigException($this->wrapper->getErrorMessage()
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
    public function setTimeout(int $value) : bool
    {
        if (!is_int($value) || $value < 0) {
            throw new ConfigException("[{$value}] Timeout option value must be of type integer and greater than 0.");
        }

        if (!$this->wrapper->set_option(FtpWrapper::TIMEOUT_SEC, $value)) {
            throw new ConfigException($this->wrapper->getErrorMessage()
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
    public function usePassiveAddress(bool $value) : bool
    {
        if (!is_bool($value)) {
            throw new ConfigException("[{$value}] usePassiveAddress option value must be of type boolean.");
        }

        if (!$this->wrapper->set_option(FtpWrapper::USEPASVADDRESS, $value)) {
            throw new ConfigException($this->wrapper->getErrorMessage()
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
    public function setAutoSeek(bool $value) : bool
    {
        if (!is_bool($value)) {
            throw new ConfigException("[{$value}] AutoSeek option value must be of type boolean.");
        }

        if (!$this->wrapper->set_option(FtpWrapper::AUTOSEEK, $value)) {
            throw new ConfigException($this->wrapper->getErrorMessage()
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
    public function getTimeout() : int
    {
        if (!$value = $this->wrapper->get_option(FtpWrapper::TIMEOUT_SEC)) {
            throw new ConfigException($this->wrapper->getErrorMessage()
                ?: "Unable to get FTP timeout option value.");
        }

        return $value;
    }

    /**
     * Checks if the autoSeek option enabled or not.
     *
     * @return bool
     */
    public function isAutoSeek() : bool
    {
        return $this->wrapper->get_option(FtpWrapper::AUTOSEEK);
    }

    /**
     * Checks if the passive address returned in the PASV response
     * is used by the control channel or not.
     *
     * @return bool
     */
    public function isUsePassiveAddress() : bool
    {
        return $this->wrapper->get_option(FtpWrapper::USEPASVADDRESS);
    }
}
