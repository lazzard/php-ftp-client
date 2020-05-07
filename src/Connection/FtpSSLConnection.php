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

use Lazzard\FtpClient\Command\FtpCommand;
use Lazzard\FtpClient\Exception\ConnectionException;

/**
 * Represents an -Explicit FTP over TLS/TLS- connection
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpSSLConnection extends FtpConnection
{
    /** @var FtpCommand */
    protected $command;

    /**
     * FtpSSLConnection constructor.
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param int    $port
     * @param int    $timeout
     *
     * @throws ConnectionException
     */
    public function __construct($host, $username, $password, $port = 21, $timeout = 90)
    {
        if ( ! extension_loaded('openssl')) {
            throw new ConnectionException("openssl extension not loaded.");

        } elseif ( ! function_exists('ftp_ssl_connect')) {
            throw new ConnectionException("
                It seems that either the FTP module or openssl extension are not statically built into your PHP.
                If you have to use an SSL-FTP connection, then you must compile your own PHP binaries using the right configuration options."
            );
        }

        parent::__construct($host, $username, $password, $port, $timeout);
    }

    /**
     * @return bool
     *
     * @throws ConnectionException
     */
    public function secureDataChannel()
    {
        if ( ! $this->protectBufferSize(0)) {
            if ($this->command->rawRequest("PROT P")->getResponseCode() !== 200) {
                throw new ConnectionException(
                    "Securing data channel was failed."
                );
            }
        } else {
            throw new ConnectionException("Unable to set buffer connection size.");
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function login()
    {
        if ( ! $this->tlsAuthentication()) {
            if ( ! $this->sslAuthentication()) {
                throw new ConnectionException("Authentication TLS/SSL was failed.");
            }
        }

        return parent::login();
    }

    /**
     * @return bool|resource
     *
     * @throws ConnectionException
     */
    protected function connect()
    {
        if ( ! $this->stream = $this->wrapper->ssl_connect($this->getHost(), $this->getPort(), $this->getTimeout())
        ) {
            throw new ConnectionException(ConnectionException::getFtpServerError()
                ?: "SSL connection failed to FTP server."
            );
        }

        $this->command = new FtpCommand($this);
        $this->wrapper->setConnection($this);

        return $this->stream;
    }

    /**
     * @param int $size
     *
     * @return bool|string
     */
    protected function protectBufferSize($size)
    {
        return ($this->command->rawRequest(sprintf("PBSZ %s", $size))->getResponseCode() !== 200);
    }

    /**
     * @return bool|string
     */
    protected function tlsAuthentication()
    {
        return ($this->command->rawRequest('AUTH TLS')->getResponseCode() === 234);
    }

    /**
     * @return bool|string
     */
    protected function sslAuthentication()
    {
        return ($this->command->rawRequest('AUTH SSL')->getResponseCode() === 234);
    }
}