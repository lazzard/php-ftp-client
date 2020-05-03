<?php


namespace Lazzard\FtpClient\Connection;

use Lazzard\FtpClient\Exception\ConnectionException;
use Lazzard\FtpClient\FtpWrapper;

/**
 * Class FtpConnection represents a regular FTP connection (not secure).
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

    /** @var string */
    protected $username;

    /* @var string */
    protected $password;

    /** @var int */
    protected $port;

    /** @var int */
    protected $timeout;

    /**
     * FtpConnection constructor.
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param int    $port    [optional]
     * @param int    $timeout [optional]
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
     * {@inheritDoc}
     *
     * @throws ConnectionException
     */
    public function getStream()
    {
        if ( ! is_resource($this->stream)) {
            throw new ConnectionException(
                "Invalid FTP stream resource, try to reopen the connection to FTP server."
            );
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
    public function open($useSsl = false)
    {
        $this->connect($useSsl);
        $this->login();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function close()
    {
        if ( ! $this->wrapper->close()) {
            throw new ConnectionException(ConnectionException::getFtpServerError()
                ?: "Failed to closing FTP connection."
            );
        }

        return true;
    }

    /**
     * @param bool $useSsl
     *
     * @return bool|resource
     *
     * @throws ConnectionException
     */
    protected function connect($useSsl = false)
    {
        if ( ! $useSsl) {
            if ( ! ($stream = $this->wrapper->connect($this->getHost(), $this->getPort(), $this->getTimeout()))
            ) {
                throw new ConnectionException(ConnectionException::getFtpServerError()
                    ?: "Connection failed to remote server."
                );
            }
        } elseif ( ! function_exists('ftp_ssl_connect')) {
            throw new ConnectionException("
                It seems that either the FTP module or openssl extension are not statically built into your PHP.
                If you have to use an SSL-FTP connection, then you must compile your own PHP binaries using the right configuration options.
            ");
        } elseif ( ! extension_loaded('openssl')) {
            throw new ConnectionException("openssl extension not loaded.");
        } elseif ( ! $stream = $this->wrapper->ssl_connect($this->getHost(), $this->getPort(), $this->getTimeout())
        ) {
            throw new ConnectionException(ConnectionException::getFtpServerError()
                ?: "SSL connection failed to FTP server."
            );
        }

        $this->stream = $stream;
        $this->wrapper->setConnection($this);

        return $stream;
    }

    /**
     * @return bool
     *
     * @throws ConnectionException
     */
    protected function login()
    {
        if ( ! $this->wrapper->login($this->getUsername(), $this->getPassword())) {
            throw new ConnectionException(ConnectionException::getFtpServerError()
                ?: "Login into the FTP server was failed."
            );
        }

        return true;
    }

}