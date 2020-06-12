<?php

/**
 * This file is part of the Lazzard/php-ftp-client package.
 *
 * (c) El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lazzard\FtpClient;

use Lazzard\FtpClient\Connection\ConnectionInterface;

/**
 * Wrapping the necessary FTP extension functions and constants for FTP client functionality
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpWrapper
{
    /**
     * Php FTP predefined constants aliases
     */
    const TIMEOUT_SEC = FTP_TIMEOUT_SEC;
    const AUTOSEEK    = FTP_AUTOSEEK;
    const ASCII       = FTP_ASCII;
    const BINARY      = FTP_BINARY;
    const FAILED      = FTP_FAILED;
    const FINISHED    = FTP_FINISHED;
    const MOREDATA    = FTP_MOREDATA;

    /** @var ConnectionInterface */
    protected $connection;

    /**
     * FtpWrapper constructor.
     *
     * @param ConnectionInterface $connection
     */
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
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    public function login($username, $password)
    {
        return @ftp_login($this->connection->getStream(), $username, $password);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-close.php
     *
     * @return bool
     */
    public function close()
    {
        return ftp_close($this->connection->getStream());
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
        return ftp_raw($this->connection->getStream(), $command);
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
        return ftp_exec($this->connection->getStream(), $command);
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
        return @ftp_site($this->connection->getStream(), $command);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-pasv.php
     *
     * @param bool $bool
     *
     * @return bool
     */
    public function pasv($bool)
    {
        return ftp_pasv($this->connection->getStream(), $bool);
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
        return @ftp_set_option($this->connection->getStream(), $option, $value);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-set-option.php
     *
     * @param int $option
     *
     * @return mixed
     */
    public function getOption($option)
    {
        return ftp_get_option($this->connection->getStream(), $option);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-chdir.php
     *
     * @param string $directory
     *
     * @return bool
     */
    public function chdir($directory)
    {
        return @ftp_chdir($this->connection->getStream(), $directory);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-pwd.php
     *
     * @return string|false
     */
    public function pwd()
    {
        return @ftp_pwd($this->connection->getStream());
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
        return @ftp_nlist($this->connection->getStream(), $directory);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-cdup.php
     *
     * @return bool
     */
    public function cdup()
    {
        return @ftp_cdup($this->connection->getStream());
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
        return @ftp_rawlist($this->connection->getStream(), $directory, $recursive);
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
        return @ftp_delete($this->connection->getStream(), $remoteFile);
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
        return @ftp_mdtm($this->connection->getStream(), $remoteFile);
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
        return ftp_rmdir($this->connection->getStream(), $directory);
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
        return ftp_mkdir($this->connection->getStream(), $directory);
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
        return ftp_size($this->connection->getStream(), $remoteFile);
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
        return @ftp_rename($this->connection->getStream(), $oldName, $newName);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-systype.php
     *
     * @return string|bool
     */
    public function systype()
    {
        return @ftp_systype($this->connection->getStream());
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-alloc.php
     *
     * @param int         $size
     * @param string|null $result
     *
     * @return bool
     */
    public function alloc($size, $result = null)
    {
        // TODO reference $result testing
        return @ftp_alloc($this->connection->getStream(), $size, $result);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-get.php
     *
     * @param string $localFile
     * @param string $remoteFile
     * @param int    $mode
     * @param int    $resumePos [optional]
     *
     * @return bool
     */
    public function get($localFile, $remoteFile, $mode, $resumePos = 0)
    {
        return @ftp_get($this->connection->getStream(), $localFile, $remoteFile, $mode, $resumePos);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-nb-get.php
     *
     * @param string $localFile
     * @param string $remoteFile
     * @param int    $mode
     * @param int    $resumePos [optional]
     *
     * @return int
     */
    public function nbGet($localFile, $remoteFile, $mode, $resumePos = 0)
    {
        return ftp_nb_get($this->connection->getStream(), $localFile, $remoteFile, $mode, $resumePos);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-nb-continue.php
     *
     * @return int
     */
    public function nbContinue()
    {
        return @ftp_nb_continue($this->connection->getStream());
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-nb-continue.php
     *
     * @param string $remoteFile
     * @param string $localFile
     * @param int    $mode
     * @param int    $startPos [optional]
     *
     * @return bool
     */
    public function put($remoteFile, $localFile, $mode, $startPos = 0)
    {
        return @ftp_put($this->connection->getStream(), $remoteFile, $localFile, $mode, $startPos);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-fput.php
     *
     * @param string   $remoteFile
     * @param resource $handle
     * @param int      $mode
     * @param int      $startPos [optional]
     *
     * @return bool
     */
    public function fput($remoteFile, $handle, $mode, $startPos = 0)
    {
        return ftp_fput($this->connection->getStream(), $remoteFile, $handle, $mode, $startPos);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-nb-fput.php
     *
     * @param string   $remoteFile
     * @param resource $handle
     * @param int      $mode
     * @param int      $startPos [optional]
     *
     * @return int
     */
    public function nbFput($remoteFile, $handle, $mode, $startPos = 0)
    {
        return ftp_nb_fput($this->connection->getStream(), $remoteFile, $handle, $mode, $startPos);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-nb-fput.php
     *
     * @param string $host
     * @param int    $port
     * @param int    $timeout
     *
     * @return false|resource
     */
    public function sslConnect($host, $port = 21, $timeout = 90)
    {
        return @ftp_ssl_connect($host, $port, $timeout);
    }

    /**
     * @link https://www.php.net/manual/en/function.ftp-chmod.php
     *
     * @param int    $mode
     * @param string $filename
     *
     * @return false|int
     */
    public function chmod($mode, $filename)
    {
        return @ftp_chmod($this->connection->getStream(), $mode, $filename);
    }
}
