<?php

namespace Lazzard\FtpClient;

use Lazzard\FtpClient\Exceptions\FtpClientLogicException;
use Lazzard\FtpClient\Exceptions\FtpClientRuntimeException;

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
     * FtpClient constructor.
     */
    public function __construct()
    {

    }

    /**
     * Handle unsupportable FTP functions by FtpClient.
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     *
     * @throws \Lazzard\FtpClient\Exceptions\FtpClientLogicException
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
     * @return resource
     */
    public function getFtpStream()
    {
        return $this->ftpStream;
    }

    /**
     * @param resource $ftpStream
     */
    public function setFtpStream($ftpStream)
    {
        $this->ftpStream = $ftpStream;
    }

    /**
     * Open FTP connection.
     *
     * @param string $host Host name
     * @param int    $port
     *
     * @return bool
     *
     * @throws \Lazzard\FtpClient\Exceptions\FtpClientRuntimeException
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
     * @param $username
     * @param $password
     *
     * @return bool
     *
     * @throws \Lazzard\FtpClient\Exceptions\FtpClientRuntimeException
     */
    public function login($username, $password)
    {
        if (!is_null($this->getFtpStream()))
            return @ftp_login($this->getFtpStream(), $username, $password);

        throw FtpClientRuntimeException::ftpServerLoggingFailed();
    }


}