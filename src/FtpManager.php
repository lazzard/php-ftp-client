<?php

namespace Lazzard\FtpClient;

use Lazzard\FtpClient\Configuration\FtpConfiguration;
use Lazzard\FtpClient\Configuration\FtpConfigurationInterface;
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
    /** @var resource */
    protected $connection;

    /** @var \Lazzard\FtpClient\FtpWrapper */
    protected $ftpWrapper;

    /** @var \Lazzard\FtpClient\Configuration\FtpConfiguration */
    protected $ftpConfiguration;

    /** @var string */
    protected $currentDir;

    /**
     * FtpManager constructor.
     *
     * @param \Lazzard\FtpClient\Configuration\FtpConfigurationInterface|null $ftpConfiguration
     */
    public function __construct(FtpConfigurationInterface $ftpConfiguration = null)
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
     * @throws \Lazzard\FtpClient\Exception\FtpClientRuntimeException
     */
    public function getConnection()
    {
        if (is_resource($this->connection))
            return $this->connection;

        throw new FtpClientRuntimeException("Invalid ftp resource stream, try to reconnect to the remote server.");
    }

    /**
     * @param resource $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return \Lazzard\FtpClient\FtpWrapper
     */
    public function getFtpWrapper()
    {
        return $this->ftpWrapper;
    }

    /**
     * @param \Lazzard\FtpClient\FtpWrapper $ftpWrapper
     */
    public function setFtpWrapper($ftpWrapper)
    {
        $this->ftpWrapper = $ftpWrapper;
    }

    /**
     * Get current FTP configuration.
     *
     * @return \Lazzard\FtpClient\Configuration\FtpConfiguration
     */
    public function getFtpConfiguration()
    {
        return $this->ftpConfiguration;
    }

    /**
     * @param \Lazzard\FtpClient\Configuration\FtpConfiguration $ftpConfiguration
     */
    public function setFtpConfiguration($ftpConfiguration)
    {
        $this->ftpConfiguration = $ftpConfiguration;
    }

    /**
     * @return string
     */
    public function getCurrentDir()
    {
        return $this->getFtpWrapper()->pwd($this->getConnection());
    }

    /**
     * @param string $currentDir
     */
    public function setCurrentDir($currentDir)
    {
        $this->getFtpWrapper()->chdir($this->getConnection(), $currentDir);
        $this->currentDir = '/' . $currentDir;
    }

    /**
     * Set client ftp configuration.
     */
    private function setClientConfiguration()
    {
        $this->getFtpWrapper()->setOption(
            $this->getConnection(),
            FtpWrapper::TIMEOUT_SEC,
            $this->getFtpConfiguration()->getTimeout()
        );

        $this->getFtpWrapper()->setOption(
            $this->getConnection(),
            FtpWrapper::AUTOSEEK,
            $this->getFtpConfiguration()->isAutoSeek()
        );

        $this->getFtpWrapper()->setOption(
            $this->getConnection(),
            FtpWrapper::USEPASVADDRESS,
            $this->getFtpConfiguration()->isUsePassiveAddress()
        );

        $this->getFtpWrapper()->pasv(
            $this->getConnection(),
            $this->getFtpConfiguration()->isPassive()
        );

        $this->getFtpWrapper()->chdir(
            $this->getConnection(),
            $this->getFtpConfiguration()->getRoot()
        );

        $this->setCurrentDir($this->getFtpConfiguration()->getRoot());
    }

    /**
     * Open an FTP connection.
     *
     * @param string $host    Host name
     * @param int    $port    Default sets to port 21
     * @param int    $timeout Default value is 90
     *
     * @return bool
     *
     */
    public function connect($host, $port = 21, $timeout = 90)
    {
        if (($connection = $this->getFtpWrapper()->connect(
            $host,
            $port,
            $timeout)) !== false)
        {
            $this->setConnection($connection);
            return true;
        }

        throw new FtpClientRuntimeException("Connection failed to remote server.");
    }

    /**
     * Logging in to an FTP server.
     *
     * @param string $username
     * @param string $password
     *
     * @return bool
     *
     * @throws \Lazzard\FtpClient\Exception\FtpClientRuntimeException
     */
    public function login($username, $password)
    {
        if ($this->getFtpWrapper()->login($this->getConnection(), $username, $password) !== false) {
            $this->setClientConfiguration();
            return true;
        }

        throw new FtpClientRuntimeException("Logging failed to remote server.");
    }

    /**
     * Close an FTP connection.
     *
     * @return bool
     */
    public function close()
    {
        if ($this->getFtpWrapper()->close($this->getConnection()) === false)
            throw new FtpClientRuntimeException("Failed to closing FTP connection.");

        return true;
    }
}