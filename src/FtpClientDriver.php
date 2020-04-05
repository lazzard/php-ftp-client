<?php


namespace Lazzard\FtpClient;

use Lazzard\FtpClient\Configuration\FtpConfiguration;
use Lazzard\FtpClient\Exception\FtpClientLogicException;
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
    protected $ftpStream;
    /** @var \Lazzard\FtpClient\Configuration\FtpConfiguration */
    private $ftpConfiguration;

    /**
     * FtpClientDriver constructor.
     *
     * @param \Lazzard\FtpClient\Configuration\FtpConfiguration|null $ftpConfiguration
     *
     * @throws \Lazzard\FtpClient\Configuration\Exception\FtpConfigurationOptionException
     */
    public function __construct(FtpConfiguration $ftpConfiguration = null)
    {
        if (is_null($ftpConfiguration)) {
            $this->ftpConfiguration = new FtpConfiguration();
        } else {
            $this->ftpConfiguration = $ftpConfiguration;
        }
    }

    /**
     * Get ftp stream resource.
     *
     * @return resource
     *
     * @throws \Lazzard\FtpClient\Exception\FtpClientRuntimeException
     */
    public function getFtpStream()
    {
        if (is_resource($this->ftpStream))
            return $this->ftpStream;

        throw FtpClientLogicException::invalidFtpResource();
    }

    /**
     * Get FTP stream resource.
     * @param resource $ftpStream
     */
    public function setFtpStream($ftpStream)
    {
        $this->ftpStream = $ftpStream;
    }

    /**
     * Get FTP configuration.
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
        if (($ftpStream = @ftp_connect($host, $port, $this->ftpConfiguration->getTimeout())) !== false) {
            $this->setftpStream($ftpStream);
            ftp_pasv($this->ftpStream, $this->ftpConfiguration->isPassive());
            return true;
        }

        throw FtpClientRuntimeException::ftpServerConnectionFailed();
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
        if (is_null($this->getFtpStream()) === false) {
            if (@ftp_login($this->getFtpStream(), $username, $password) == false)
                throw FtpClientRuntimeException::ftpServerLoggingFailed();
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
        if (ftp_close($this->getFtpStream()) === false)
            throw FtpClientRuntimeException::closingFtpConnectionFailed();

        return true;
    }

}