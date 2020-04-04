<?php


namespace Lazzard\FtpClient;

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
    private $ftpStream;

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

        throw FtpClientRuntimeException::invalidFtpResource();
    }

    /**
     * @param resource $ftpStream
     */
    public function setFtpStream($ftpStream)
    {
        $this->ftpStream = $ftpStream;
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
        if (($ftpStream = @ftp_connect($host, $port)) !== false) {
            $this->setftpStream($ftpStream);
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