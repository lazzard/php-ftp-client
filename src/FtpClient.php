<?php

namespace Lazzard\FtpClient;

use Lazzard\FtpClient\Exception\FtpClientLogicException;
use Lazzard\FtpClient\Exception\FtpClientRuntimeException;

/**
 * Class FtpClient
 *
 * @since 1.0
 * @package Lazzard\FtpClient
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class FtpClient {
    /** @var resource */
    private $ftpStream;

    /**
     * FtpClient __call.
     *
     * Handle unsupportable FTP functions by FtpClient.
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     *
     * @throws \Lazzard\FtpClient\Exception\FtpClientLogicException
     */
    public function __call($name, $arguments)
    {
        $ftpFunction = "ftp_" . $name;

        if (function_exists($ftpFunction) == true) {
            array_unshift($arguments, $this->getFtpStream());
            return call_user_func_array($ftpFunction, $arguments);
        }

        throw FtpClientLogicException::invalidFtpFunction($ftpFunction);
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