<?php


namespace Lazzard\FtpClient;

use Lazzard\FtpClient\Configuration\FtpConfiguration;
use Lazzard\FtpClient\Exception\FtpClientRuntimeException;

/**
 * Class FtpDriver
 *
 * Manage FTP connection and setting FTP runtime options.
 *
 * @since 1.0
 * @package Lazzard\FtpClient
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
abstract class FtpDriver
{
    /** @var resource */
    protected $connection;

    /** @var \Lazzard\FtpClient\FtpWrapper */
    protected $ftpWrapper;

    /** @var \Lazzard\FtpClient\Configuration\FtpConfiguration */
    private $ftpConfiguration;

    /**
     * FtpDriver constructor.
     *
     * @param \Lazzard\FtpClient\Configuration\FtpConfiguration|null $ftpConfiguration
     *
     * @throws \ReflectionException
     */
    public function __construct(FtpConfiguration $ftpConfiguration = null)
    {
        if (is_null($ftpConfiguration)) {
            $this->ftpConfiguration = new FtpConfiguration();
        }

        $this->ftpConfiguration = $ftpConfiguration;
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

            $this->getFtpWrapper()->setOption(
                $this->getConnection(),
               FtpWrapper::TIMEOUT_SEC,
                $this->getFtpConfiguration()->isAutoSeek()
            );

            $this->getFtpWrapper()->setOption(
                $this->getConnection(),
                FtpWrapper::AUTOSEEK,
                $this->getFtpConfiguration()->getTimeout()
            );

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
        if ($this->getFtpWrapper()->login($this->getConnection(), $username, $password) === true) {
            $this->getFtpWrapper()->pasv($this->getConnection(), $this->getFtpConfiguration()->isPassive());
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