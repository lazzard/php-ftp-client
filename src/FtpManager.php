<?php

namespace Lazzard\FtpClient;

use Lazzard\FtpClient\Command\FtpCommand;
use Lazzard\FtpClient\Configuration\Exception\FtpConfigurationLogicException;
use Lazzard\FtpClient\Configuration\Exception\FtpConfigurationRuntimeException;
use Lazzard\FtpClient\Configuration\FtpConfiguration;
use Lazzard\FtpClient\Configuration\ConfigurationInterface;
use Lazzard\FtpClient\Exception\FtpClientRuntimeException;

/**
 * Class FtpManager
 *
 * Manage FTP connection and setting FTP client configuration.
 *
 * @since 1.0
 * @package Lazzard\FtpClient
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
abstract class FtpManager
{
    /**
     * Php FTP predefined constants aliases
     */
    const TIMEOUT_SEC    = FTP_TIMEOUT_SEC;
    const AUTOSEEK       = FTP_AUTOSEEK;
    const USEPASVADDRESS = FTP_USEPASVADDRESS;

    /** @var resource */
    protected $connection;

    /** @var FtpWrapper */
    protected $ftpWrapper;

    /** @var FtpConfiguration */
    protected $ftpConfiguration;

    /** @var FtpCommand */
    protected $ftpCommand;

    /** @var string */
    protected $currentDir;

    /**
     * FtpManager constructor.
     *
     * @param ConfigurationInterface|null $ftpConfiguration
     */
    public function __construct(ConfigurationInterface $ftpConfiguration = null)
    {
        if (is_null($ftpConfiguration)) {
            $this->ftpConfiguration = new FtpConfiguration();
        } else {
            $this->ftpConfiguration = $ftpConfiguration;
        }

        $this->ftpWrapper = new FtpWrapper();
    }

    /**
     * Get current FTP stream resource.
     *
     * @return resource
     *
     * @throws FtpClientRuntimeException
     */
    public function getConnection()
    {
        if (is_resource($this->connection)) {
            return $this->connection;
        }

        throw new FtpClientRuntimeException("Invalid FTP resource stream, try to reconnect to the remote server.");
    }

    /**
     * @param resource $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get current FTP configuration.
     *
     * @return FtpConfiguration
     */
    public function getFtpConfiguration()
    {
        return $this->ftpConfiguration;
    }

    /**
     * @param FtpConfiguration $ftpConfiguration
     */
    public function setFtpConfiguration($ftpConfiguration)
    {
        $this->ftpConfiguration = $ftpConfiguration;
        $this->setClientConfiguration();
    }

    /**
     * @return string
     */
    public function getCurrentDir()
    {
        return $this->ftpWrapper->pwd($this->getConnection());
    }

    /**
     * @param string $currentDir
     *
     * @throws FtpClientRuntimeException
     */
    public function setCurrentDir($currentDir)
    {
        if ($this->ftpWrapper->chdir($this->getConnection(), $currentDir) !==
        true) {
            throw new FtpClientRuntimeException("Cannot change to the giving directory.");
        }

        $this->currentDir = $currentDir;
    }

    /**
     * Set client ftp configuration.
     */
    private function setClientConfiguration()
    {
        $this->setOption(
            self::TIMEOUT_SEC,
            $this->getFtpConfiguration()->getTimeout()
        );

        $this->setOption(
            self::AUTOSEEK,
            $this->getFtpConfiguration()->isAutoSeek()
        );

        $this->setOption(
            self::USEPASVADDRESS,
            $this->getFtpConfiguration()->isUsePassiveAddress()
        );

        $this->setPassive($this->getFtpConfiguration()->isPassive());

        $this->setCurrentDir($this->getFtpConfiguration()->getRoot());
    }

    /**
     * Opens an FTP connection.
     *
     * @param string $host    Host name
     * @param int    $port    Default sets to port 21
     * @param int    $timeout Default value is 90
     *
     * @return bool
     *
     * @throws FtpClientRuntimeException
     */
    public function connect($host, $port = 21, $timeout = 90)
    {
        if (($connection = $this->ftpWrapper->connect(
            $host,
            $port,
            $timeout)) !== false)
        {
            $this->setConnection($connection);
            $this->ftpCommand = new FtpCommand($this->getConnection());
            return true;
        }

        throw new FtpClientRuntimeException("Connection failed to remote server.");
    }

    /**
     * Opens a secure FTP connection.
     *
     * @param string $host    Host name
     * @param int    $port    Default sets to port 21
     * @param int    $timeout Default value is 90
     *
     * @return bool
     *
     * @throws FtpClientRuntimeException
     */
    public function sslConnect($host, $port = 21, $timeout = 90)
    {
        if (($connection = $this->ftpWrapper->ssl_connect(
                $host,
                $port,
                $timeout)) !== false)
        {
            $this->setConnection($connection);
            $this->ftpCommand = new FtpCommand($this->getConnection());
            return true;
        }

        throw new FtpClientRuntimeException("SSL connection failed to remote server.");
    }

    /**
     * Logging in to an FTP server.
     *
     * @param string $username
     * @param string $password
     *
     * @return bool
     *
     * @throws FtpClientRuntimeException
     */
    public function login($username, $password)
    {
        if ($this->ftpWrapper->login($this->getConnection(), $username, $password) !== true) {
            throw new FtpClientRuntimeException("Logging failed to remote server.");
        }

        $this->setClientConfiguration();
        return true;
    }

    /**
     * Close an FTP connection.
     *
     * @return bool
     * 
     * @throws FtpClientRuntimeException
     */
    public function close()
    {
        if ($this->ftpWrapper->close($this->getConnection()) !== true) {
            throw new FtpClientRuntimeException("Failed to closing FTP connection.");
        }

        return true;
    }

    /**
     * Set FTP runtime options.
     *
     * @param $option
     * @param $value
     *
     * @return bool
     * 
     * @throws FtpClientRuntimeException
     * @throws \Lazzard\FtpClient\Exception\FtpClientLogicException
     */
    public function setOption($option, $value)
    {
        $settings = [
          self::TIMEOUT_SEC,
          self::AUTOSEEK,
          self::USEPASVADDRESS
        ];

        if (in_array($option, $settings) !== true) {
            throw new FtpConfigurationLogicException("{$option} is invalid FTP runtime option.");
        }

        if ($this->ftpWrapper->setOption(
            $this->getConnection(), 
            $option, 
            $value) !== true) {
            throw new FtpClientRuntimeException("Unable to set FTP option.");
        }

        return true;
    }

    /**
     * Turn the passive mode on or off.
     *
     * Notice that the active mode is the default mode.
     *
     * @param $bool
     *
     * @return bool
     * 
     * @throws FtpClientRuntimeException
     */
    public function setPassive($bool)
    {
        if ($this->ftpWrapper->pasv($this->getConnection(), $bool) !== true) {
            throw new FtpConfigurationRuntimeException("Unable to switch FTP mode.");
        }

        return true;
    }

}