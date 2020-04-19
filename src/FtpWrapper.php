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
        return ftp_connect($host, $port, $timeout);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-ssl-connect.php
     *
     * @param string $host
     * @param int    $port
     * @param int    $timeout
     *
     * @return false|resource
     */
    public function ssl_connect($host, $port = 21, $timeout = 90)
    {
        return @ftp_ssl_connect($host, $port, $timeout);
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
     * @param resource $ftpStream
     * @param int      $option
     * @param mixed    $value
     *
     * @return bool
     */
    public function setOption($ftpStream, $option, $value)
    {
        return @ftp_set_option($ftpStream, $option, $value);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-set-option.php
     *
     * @param resource $ftpStream
     * @param int      $option
     *
     * @return mixed
     */
    public function getOption($ftpStream, $option)
    {
        return @ftp_get_option($ftpStream, $option);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-chdir.php
     *
     * @param resource $ftpStream
     * @param string   $directory
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
     * @param resource $ftpStream
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
     * @param resource $ftpStream
     * @param string   $directory
     *
     * @return array|false
     */
    public function nlist($ftpStream, $directory)
    {
        return ftp_nlist($ftpStream, $directory);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-cdup.php
     *
     * @param resource $ftpStream
     *
     * @return bool
     */
    public function cdup($ftpStream)
    {
        return ftp_cdup($ftpStream);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-raw.php
     *
     * @param resource $ftpStream
     * @param string   $command
     *
     * @return array
     */
    public function raw($ftpStream, $command)
    {
        return ftp_raw($ftpStream, $command);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-exec.php
     *
     * @param resource $ftpStream
     * @param string   $command
     *
     * @return bool
     */
    public function exec($ftpStream, $command)
    {
        return ftp_exec($ftpStream, $command);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-exec.php
     *
     * @param resource $ftpStream
     * @param string   $command
     *
     * @return bool
     */
    public function site($ftpStream, $command)
    {
        return ftp_site($ftpStream, $command);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-rawlist.php
     *
     * @param resource $ftpStream
     * @param string   $directory
     * @param bool     $recursive
     *
     * @return array
     */
    public function rawlist($ftpStream, $directory, $recursive = false)
    {
        return ftp_rawlist($ftpStream, $directory, $recursive);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-delete.php
     *
     * @param resource $ftpStream
     * @param string   $remoteFile
     *
     * @return bool
     */
    public function delete($ftpStream, $remoteFile)
    {
        return ftp_delete($ftpStream, $remoteFile);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-mdtm.php
     *
     * @param resource $ftpStream
     * @param string   $remoteFile
     *
     * @return int
     */
    public function mdtm($ftpStream, $remoteFile)
    {
        return ftp_mdtm($ftpStream, $remoteFile);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-rmdir.php
     *
     * @param resource $ftpStream
     * @param string   $directory
     *
     * @return bool
     */
    public function rmdir($ftpStream, $directory)
    {
        return ftp_rmdir($ftpStream, $directory);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-rmdir.php
     *
     * @param resource $ftpStream
     * @param string   $directory
     *
     * @return string|false
     */
    public function mkdir($ftpStream, $directory)
    {
        return @ftp_mkdir($ftpStream, $directory);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-size.php
     *
     * @param resource $ftpStream
     * @param string   $remoteFile
     *
     * @return int
     */
    public function size($ftpStream, $remoteFile)
    {
        return ftp_size($ftpStream, $remoteFile);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-rename.php
     *
     * @param resource $ftpStream
     * @param string   $oldName
     * @param string   $newName
     *
     * @return bool
     */
    public function rename($ftpStream, $oldName, $newName)
    {
        return ftp_rename($ftpStream, $oldName, $newName);
    }
}
