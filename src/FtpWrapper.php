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
use Lazzard\FtpClient\Exception\WrapperException;

/**
 * A simple class wrapper for the FTP extension functions (ftp_*).
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpWrapper
{
    /**
     * Php FTP predefined constants aliases
     */
    const TIMEOUT_SEC    = FTP_TIMEOUT_SEC;
    const AUTOSEEK       = FTP_AUTOSEEK;
    const USEPASVADDRESS = FTP_USEPASVADDRESS;
    const ASCII          = FTP_ASCII;
    const BINARY         = FTP_BINARY;
    const FAILED         = FTP_FAILED;
    const FINISHED       = FTP_FINISHED;
    const MOREDATA       = FTP_MOREDATA;

    /** @var ConnectionInterface */
    protected $connection;

    /** @var string */
    protected $errorMessage;

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
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param string     $func
     * @param array|null $args
     *
     * @return mixed
     *
     * @throws WrapperException
     */
    public function __call($func, $args = null)
    {
        $funcName = "ftp_$func";
        if (!function_exists($funcName)) {
            throw new WrapperException("$funcName() doesn't exists.");
        }

        if (!in_array($func, ['connect', 'ssl_connect'])) {
            array_unshift($args, $this->connection->getStream());
        }

        set_error_handler(function () {
            $this->errorMessage = func_get_args()[1];
        });

        try {
            return call_user_func_array($funcName, $args);
        } finally {
            restore_error_handler();
        }
    }
}
