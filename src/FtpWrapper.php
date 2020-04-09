<?php

namespace Lazzard\FtpClient;

/**
 * Class FtpWrapper
 *
 * Wrapper class for php FTP functions & constants.
 *
 * @since 1.0
 * @package Lazzard\FtpClient
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class FtpWrapper
{
    /**
     * @link https://www.php.net/manual/en/function.ftp-connect.php
     *
     * @param string $host
     * @param int    $port
     * @param int    $timeout
     *
     * @return false|resource
     */
    public function connect($host, $port = 21, $timeout = 90)
    {
        return @ftp_connect($host, $port, $timeout);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-login.php
     *
     * @param resource $ftpStream
     * @param string   $username
     * @param string   $password
     *
     * @return bool
     */
    public function login($ftpStream, $username, $password)
    {
        return @ftp_login($ftpStream, $username, $password);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-close.php
     *
     * @param resource $ftpStream
     *
     * @return bool
     */
    public function close($ftpStream)
    {
        return ftp_close($ftpStream);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-pasv.php
     *
     * @param resource $ftpStream
     * @param bool     $pasv
     *
     * @return bool
     */
    public function pasv($ftpStream, $pasv)
    {
        return ftp_pasv($ftpStream, $pasv);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-set-option.php
     *
     * @param $ftpStream
     * @param $option
     * @param $value
     *
     * @return bool
     */
    public function setOption($ftpStream, $option, $value)
    {
        return @ftp_set_option($ftpStream, $option, $value);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-chdir.php
     *
     * @param $ftpStream
     * @param $directory
     *
     * @return bool
     */
    public function chdir($ftpStream, $directory)
    {
        return @ftp_chdir($ftpStream, $directory);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-pwd.php
     *
     * @param $ftpStream
     *
     * @return string
     */
    public function pwd($ftpStream)
    {
        return ftp_pwd($ftpStream);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-nlist.php
     *
     * @param $ftpStream
     * @param $directory
     *
     * @return array
     */
    public function nlist($ftpStream, $directory)
    {
        return ftp_nlist($ftpStream, $directory);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-cdup.php
     *
     * @param $ftpStream
     *
     * @return bool
     */
    public function cdup($ftpStream)
    {
        return ftp_cdup($ftpStream);
    }

    /**
     * @param $ftpStream
     * @param $command
     *
     * @return array
     */
    public function raw($ftpStream, $command)
    {
        return ftp_raw($ftpStream, $command);
    }

}
