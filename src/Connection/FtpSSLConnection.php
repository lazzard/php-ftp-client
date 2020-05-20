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

/**
 * Represents an -Explicit FTP over TLS/SSL- FTP connection.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpSSLConnection extends FtpConnection
{
    /**
     * {@inheritDoc}
     *
     * @throws ConnectionException
     */
    public function __construct($host, $username, $password, $port = 21, $timeout = 90)
    {
        if (!extension_loaded('openssl')) {
            throw new ConnectionException("openssl extension required to establish a secure connection, 
            please enable it.");
        } elseif (!function_exists('ftp_ssl_connect')) {
            throw new ConnectionException("It seems that either the FTP module or openssl extension are not 
            statically built into your PHP. If you have to use an SSL-FTP connection, you must compile your 
            own PHP binaries using the right configuration options.");
        }

        parent::__construct($host, $username, $password, $port, $timeout);
    }


    /**
     * @inheritDoc
     */
    protected function connect()
    {
        if (!$this->stream = $this->wrapper->sslConnect($this->getHost(), $this->getPort(), $this->getTimeout())) {
            throw new ConnectionException(ConnectionException::getFtpServerError()
                ?: "SSL connection failed to the FTP server.");
        }

        $this->wrapper->setConnection($this);

        return $this->stream;
    }
}
