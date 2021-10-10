<?php declare(strict_types=1);

/**
 * This file is part of the Lazzard/php-ftp-client package.
 *
 * (c) El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lazzard\FtpClient\Connection;

use Lazzard\FtpClient\FtpWrapper;
use Lazzard\FtpClient\Exception\ConnectionException;

/**
 * Abstract an FTP connection class implementations.
 *
 * @since  1.2.6
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
abstract class Connection implements ConnectionInterface
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

    /** @var bool */
    protected $isConnected;

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
    public function __construct(string $host, string $username, string $password, int $port = 21, int $timeout = 90)
    {
        $this->host        = $host;
        $this->username    = $username;
        $this->password    = $password;
        $this->port        = $port;
        $this->timeout     = $timeout;
        $this->isConnected = false;

        $this->wrapper = new FtpWrapper($this);
    }

    /**
     * @param FtpWrapper $wrapper
     *
     * @return void
     */
    public function setWrapper(FtpWrapper $wrapper) : void
    {
        $this->wrapper = $wrapper;
    }

    /**
     * @since 1.5.3
     *
     * @return FtpWrapper
     */
    public function getWrapper() : FtpWrapper
    {
        return $this->wrapper;
    }

    /**
     * {@inheritDoc}
     * 
     * @throws ConnectionException
     */
    public function getStream()
    {
        if (!is_resource($this->stream)) {
            throw new ConnectionException("Invalid FTP stream resource, try to reopen the FTP connection.");
        }

        return $this->stream;
    }

    /**
     * @inheritDoc
     */
    public function getHost() : string
    {
        return $this->host;
    }

    /**
     * @inheritDoc
     */
    public function getPort() : int
    {
        return $this->port;
    }

    /**
     * @inheritDoc
     */
    public function getTimeout() : int
    {
        return $this->timeout;
    }

    /**
     * @inheritDoc
     */
    public function getUsername() : string
    {
        return $this->username;
    }

    /**
     * @inheritDoc
     */
    public function getPassword() : string
    {
        return $this->password;
    }

    /**
     * @inheritDoc
     */
    public function isConnected() : bool
    {
        return $this->isConnected;
    }

    /**
     * {@inheritDoc}
     *
     * @throws ConnectionException
     */
    public function open() : bool
    {
        $this->connect();
        $this->login();

        return $this->isConnected = true;
    }

    /**
     * {@inheritDoc}
     *
     * @throws ConnectionException
     */
    public function close() : bool
    {
        if (!$this->wrapper->close()) {
            throw new ConnectionException($this->wrapper->getErrorMessage()
                ?: "Unable to close the FTP connection.");
        }

        $this->isConnected = false;

        return true;
    }

    /**
     * @throws ConnectionException
     */
    protected function login() : void
    {
        if (!$this->wrapper->login($this->getUsername(), $this->getPassword())) {
            throw new ConnectionException($this->wrapper->getErrorMessage()
                ?: "Logging into the FTP server was failed.");
        }
    }

    /**
     * @throws ConnectionException
     */
    protected function connect() : void
    {
        if (!$this->isValidHost($this->host)) {
            throw new ConnectionException("[$this->host] is not a valid host name/IP.");
        }
    }

    private function isValidHost($host) : bool
    {
        return filter_var(gethostbyname($host), FILTER_VALIDATE_IP) !== false;
    }
}