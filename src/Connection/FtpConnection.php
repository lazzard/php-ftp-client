<?php


namespace Lazzard\FtpClient\Connection;

use Lazzard\FtpClient\Exception\ConnectionException;
use Lazzard\FtpClient\FtpWrapper;

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
     * @param int    $port
     * @param int    $timeout
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
            throw new ConnectionException("Invalid FTP stream resource, try to reconnect to the server.");
        }

        return $this->stream;
    }

    /**
     * @param resource $stream
     */
    public function setStream($stream)
    {
        $this->stream = $stream;
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
    public function setHost($host)
    {
        $this->host = $host;
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
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @inheritDoc
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @inheritDoc
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @inheritDoc
     */
    public function open()
    {
        $this->_connect();
        $this->_login();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function close()
    {
        if ( ! $this->wrapper->close()) {
            throw new ConnectionException(
                ConnectionException::getFtpServerError(),
                "Failed to closing FTP connection."
            );
        }

        return true;
    }

    /**
     * @return bool|resource
     *
     * @throws ConnectionException
     */
    protected function _connect()
    {
        if ( ! ($stream = $this->wrapper->connect($this->getHost(), $this->getPort(),
            $this->getTimeout()))) {
            throw new ConnectionException(
                ConnectionException::getFtpServerError()
                ?: "Connection failed to remote server."
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
    protected function _login()
    {
        if ( ! $this->wrapper->login(
            $this->getUsername(),
            $this->getPassword()
        )) {
            throw new ConnectionException(
                ConnectionException::getFtpServerError(),
                "Login into the FTP server was failed."
            );
        }

        return true;
    }

}