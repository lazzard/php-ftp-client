<?php


namespace Lazzard\FtpClient;

use Lazzard\FtpClient\Configuration\FtpConfiguration;
use Lazzard\FtpClient\Exception\FtpClientRuntimeException;

/**
 * Class FtpClientDriver manage FTP connection.
 *
 * @since 1.0
 * @package Lazzard\FtpClient
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
abstract class FtpClientDriver
{
    /** @var resource */
    protected $connection;
    /** @var \Lazzard\FtpClient\Configuration\FtpConfiguration */
    private $ftpConfiguration;

    /**
     * FtpClientDriver constructor.
     *
     * @param \Lazzard\FtpClient\Configuration\FtpConfiguration|null $ftpConfiguration
     */
    public function __construct(FtpConfiguration $ftpConfiguration = null)
    {
        if (is_null($ftpConfiguration)) {
            # FTP default configuration
            $this->ftpConfiguration = new FtpConfiguration();
        } else {
            # FTP Client configuration
            $this->ftpConfiguration = $ftpConfiguration;
        }
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
     * Get current FTP configuration.
     *
     * @return \Lazzard\FtpClient\Configuration\FtpConfiguration
     */
    public function getFtpConfiguration()
    {
        return $this->ftpConfiguration;
    }

    /**
     * Open an FTP connection.
     *
     * @param string $host Host name
     * @param int    $port
     *
     * @return bool
     *
     * @throws \Lazzard\FtpClient\Exception\FtpClientRuntimeException
     */

    public function connect($host, $port)
    {
        if (($connection = @ftp_connect($host, $port, $this->getFtpConfiguration()->getTimeout())) !== false) {
            $this->setConnection($connection);
            ftp_pasv($this->getConnection(), $this->getFtpConfiguration()->isPassive());
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
        if (is_null($this->getConnection()) === false) {
            if (@ftp_login($this->getConnection(), $username, $password) == false)
                throw new FtpClientRuntimeException("Logging failed to remote server.");
        }

        return true;
    }

    /**
     * Close an FTP connection.
     *
     * @return bool
     */
    public function close()
    {
        if (ftp_close($this->getConnection()) === false)
            throw new FtpClientRuntimeException("Failed to closing FTP connection.");

        return true;
    }

}