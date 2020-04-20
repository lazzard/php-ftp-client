<?php

namespace Lazzard\FtpClient;

use Lazzard\FtpClient\Connection\ConnectionInterface;

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
    /** @var ConnectionInterface */
    private $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param ConnectionInterface $connection
     */
    public function setConnection(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-raw.php
     *
     * @param string $command
     *
     * @return array
     */
    public function raw($command)
    {
        return ftp_raw($this->getConnection()->getStream(), $command);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-exec.php
     *
     * @param string $command
     *
     * @return bool
     */
    public function exec($command)
    {
        return ftp_exec($this->getConnection()->getStream(), $command);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-exec.php
     *
     * @param string $command
     *
     * @return bool
     */
    public function site($command)
    {
        return ftp_site($this->getConnection()->getStream(), $command);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-pasv.php
     *
     * @param bool $pasv
     *
     * @return bool
     */
    public function pasv($pasv)
    {
        return ftp_pasv($this->getConnection()->getStream(), $pasv);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-set-option.php
     *
     * @param int   $option
     * @param mixed $value
     *
     * @return bool
     */
    public function setOption($option, $value)
    {
        return ftp_set_option($this->getConnection()->getStream(), $option, $value);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-set-option.php
     *
     * @param int      $option
     *
     * @return mixed
     */
    public function getOption($option)
    {
        return ftp_get_option($this->getConnection()->getStream(), $option);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-chdir.php

     * @param string $directory
     *
     * @return bool
     */
    public function chdir($directory)
    {
        return @ftp_chdir($this->getConnection()->getStream(), $directory);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-pwd.php
     *
     * @return string
     */
    public function pwd()
    {
        return ftp_pwd($this->getConnection()->getStream());
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-nlist.php
     *
     * @param string $directory
     *
     * @return array|false
     */
    public function nlist($directory)
    {
        return ftp_nlist($this->getConnection()->getStream(), $directory);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-cdup.php
     *
     * @return bool
     */
    public function cdup()
    {
        return ftp_cdup($this->getConnection()->getStream());
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-rawlist.php
     *
     * @param string $directory
     * @param bool   $recursive
     *
     * @return array
     */
    public function rawlist($directory, $recursive = false)
    {
        return ftp_rawlist($this->getConnection()->getStream(), $directory, $recursive);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-delete.php
     *
     * @param string $remoteFile
     *
     * @return bool
     */
    public function delete($remoteFile)
    {
        return ftp_delete($this->getConnection()->getStream(), $remoteFile);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-mdtm.php
     *
     * @param string $remoteFile
     *
     * @return int
     */
    public function mdtm($remoteFile)
    {
        return ftp_mdtm($this->getConnection()->getStream(), $remoteFile);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-rmdir.php
     *
     * @param string $directory
     *
     * @return bool
     */
    public function rmdir($directory)
    {
        return ftp_rmdir($this->getConnection()->getStream(), $directory);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-rmdir.php
     *
     * @param string $directory
     *
     * @return string|false
     */
    public function mkdir($directory)
    {
        return @ftp_mkdir($this->getConnection()->getStream(), $directory);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-size.php
     *
     * @param string $remoteFile
     *
     * @return int
     */
    public function size($remoteFile)
    {
        return ftp_size($this->getConnection()->getStream(), $remoteFile);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-rename.php
     *
     * @param string $oldName
     * @param string $newName
     *
     * @return bool
     */
    public function rename($oldName, $newName)
    {
        return ftp_rename($this->getConnection()->getStream(), $oldName, $newName);
    }
}
