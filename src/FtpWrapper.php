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
use Lazzard\FtpClient\Exception\FtpClientException;

/**
 * A simple class wrapper to FTP extension functions (ftp_*).
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpWrapper
{
    /** @var ConnectionInterface */
    protected $connection;

    /** @var string */
    protected $ftpErrorMessage;

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
     * Gets the last FTP error message sent by the remote server.
     *
     * @return string|null Returns a string represent the FTP error message, null if no error detected.
     */
    public function getFtpErrorMessage()
    {
        return $this->ftpErrorMessage;
    }

    /**
     * @param string     $func
     * @param array|null $args
     *
     * @return mixed
     *
     * @throws FtpClientException
     */
    public function __call($func, $args = null)
    {
        $funcName = "ftp_$func";
        if (!function_exists($funcName)) {
            throw new FtpClientException("$funcName() doesn't exists.");
        }

        if (!in_array($func, ['connect', 'ssl_connect'])) {
            array_unshift($args, $this->connection->getStream());
        }

        set_error_handler(function () {
            $this->ftpErrorMessage = func_get_args()[1];
        });

        try {
            return call_user_func_array($funcName, $args);
        } finally {
            restore_error_handler();
        }
    }
}
