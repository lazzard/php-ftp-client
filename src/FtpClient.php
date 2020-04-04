<?php

namespace Lazzard\FtpClient;

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
     */
    public function __call($name, $arguments)
    {
        $ftpFunction = "ftp_" . $name;

        if (function_exists($ftpFunction) == true) {
            array_unshift($arguments, $this->getFtpStream());
            return call_user_func_array($ftpFunction, $arguments);
        }

        return null;
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
     */

    public function connect($host, $port)
    {
        if (($ftpStream = @ftp_connect($host, $port)) !== false) {
            $this->setftpStream($ftpStream);
            return true;
        }

        return false;
    }

    /**
     * Logging in to an FTP server.
     *
     * @param $username
     * @param $password
     *
     * @return bool
     */
    public function login($username, $password)
    {
        if (!is_null($this->getFtpStream()))
            return @ftp_login($this->getFtpStream(), $username, $password);

        return false;
    }


}