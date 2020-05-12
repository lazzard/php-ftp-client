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
     * Sends a request to the remote server to secure the data channel by
     * setting the level protection of the data channel to level P (private),
     * this method set also the connection buffer size to 0 before sending the 'PROT'
     * command to the server, if fails an exception throws.
     *
     * @return bool Returns true in success, otherwise throws an exception
     *
     * @throws ConnectionException
     */
    public function secureDataChannel()
    {
        if ( ! $this->protectBufferSize(0)) {
            $response = $this->command->raw("PROT P");

            if ($response['code'] !== 200) {
                throw new ConnectionException("Securing data channel was failed.");
            }
        } else {
            throw new ConnectionException("Unable to set buffer connection size.");
        }

        return true;
    }

    /**
     * @return bool
     *
     * @throws ConnectionException
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
     * @return bool|false|resource
     *
     * @throws ConnectionException
     */
    protected function connect()
    {
        if ( ! $this->stream = $this->wrapper->ssl_connect(
            $this->getHost(),
            $this->getPort(),
            $this->getTimeout()
        )) {
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
        return ($this->command->raw(sprintf("PBSZ %s", $size))['code'] !== 200);
    }

    /**
     * @return bool|string
     */
    protected function tlsAuthentication()
    {
        return ($this->command->raw('AUTH TLS')['code'] === 234);
    }

    /**
     * @return bool|string
     */
    protected function sslAuthentication()
    {
        return ($this->command->raw('AUTH SSL')['code'] === 234);
    }
}