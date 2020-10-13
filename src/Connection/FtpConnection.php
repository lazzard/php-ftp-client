<?php

/**
 * This file is part of the Lazzard/php-ftp-client package.
 *
 * (c) El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lazzard\FtpClient\Connection;

use Lazzard\FtpClient\Exception\ConnectionException;
use Lazzard\FtpClient\FtpWrapper;

/**
 * FtpConnection represents a regular FTP connection (not secure).
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpConnection implements ConnectionInterface
{
    /** @var FtpWrapper */
    protected $wrapper;

    /** @var resource */
    protected $stream;

    /** @var string */
    protected $host;

    /** @var int */
    protected $port;

    /** @var int */
    protected $timeout;

    /** @var string */
    protected $username;

    /* @var string */
    protected $password;

    /**
     * Prepares an FTP connection.
     *
     * @param string $host     The host name or the IP address.
     * @param string $username The client's username.
     * @param string $password The client's password.
     * @param int    $port     [optional] Specifies the port to be used to open the control channel.
     * @param int    $timeout  [optional] The connection timeout in seconds, the default sets to 90,
     *                         you can set this option any time using the {@link FtpConfig::setTimeout()} method.
     */
    public function __construct($host, $username, $password, $port = 21, $timeout = 90)
    {
        $this->host     = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port     = $port;
        $this->timeout  = $timeout;

        $this->wrapper = new FtpWrapper($this);
    }

    /**
     * @param FtpWrapper $wrapper
     */
    public function setWrapper($wrapper)
    {
        $this->wrapper = $wrapper;
    }

    /**
     * {@inheritDoc}
     *
     * @throws ConnectionException
     */
    public function getStream()
    {
        if (!is_resource($this->stream)) {
            throw new ConnectionException("Invalid FTP stream resource, try to reopen the connection to FTP server.");
        }

        return $this->stream;
    }

    /**
     * @inheritDoc
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @inheritDoc
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @inheritDoc
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @inheritDoc
     */
    public function open()
    {
        $this->connect();
        $this->login();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function close()
    {
        if (!$this->wrapper->close()) {
            throw new ConnectionException($this->wrapper->getFtpErrorMessage()
                ?: "Failed to closing FTP connection.");
        }

        return true;
    }

    /**
     * @return bool
     *
     * @throws ConnectionException
     */
    protected function connect()
    {
        if (!($this->stream = $this->wrapper->connect($this->getHost(), $this->getPort(), $this->getTimeout()))) {
            throw new ConnectionException($this->wrapper->getFtpErrorMessage()
                ?: "Connection failed to remote server.");
        }

        $this->wrapper->setConnection($this);

        return true;
    }

    /**
     * @return bool
     *
     * @throws ConnectionException
     */
    protected function login()
    {
        if (!$this->wrapper->login($this->getUsername(), $this->getPassword())) {
            throw new ConnectionException($this->wrapper->getFtpErrorMessage()
                ?: "Login into the FTP server was failed.");
        }

        return true;
    }
}
