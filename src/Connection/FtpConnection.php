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
 * FtpConnection represents a regular FTP connection (not secure).
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpConnection extends Connection
{
    /**
     * {@inheritDoc}
     * 
     * @throws ConnectionException
     */
    protected function connect()
    {
        if (!($this->stream = $this->wrapper->connect($this->getHost(), $this->getPort(), $this->getTimeout()))) {
            throw new ConnectionException($this->wrapper->getFtpErrorMessage()
                ?: "FTP connection failed to remote server.");
        }

        $this->isSecure = false;

        $this->wrapper->setConnection($this);

        return true;
    }
}
