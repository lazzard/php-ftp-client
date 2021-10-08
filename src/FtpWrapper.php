<?php declare(strict_types=1);

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
     * FTP extension constants aliases.
     */
    public const TIMEOUT_SEC    = FTP_TIMEOUT_SEC;
    public const AUTOSEEK       = FTP_AUTOSEEK;
    public const USEPASVADDRESS = FTP_USEPASVADDRESS;
    public const ASCII          = FTP_ASCII;
    public const BINARY         = FTP_BINARY;
    public const FAILED         = FTP_FAILED;
    public const FINISHED       = FTP_FINISHED;
    public const MOREDATA       = FTP_MOREDATA;

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
    public function getConnection() : ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * @param ConnectionInterface $connection
     */
    public function setConnection(ConnectionInterface $connection) : void
    {
        $this->connection = $connection;
    }

    /**
     * Gets the last FTP error message sent by the remote server.
     *
     * @return string Returns a string represent the FTP error message.
     */
    public function getErrorMessage() : string
    {
        return $this->errorMessage ?: '';
    }

    /**
     * Delegates the 'FtpWrapper::***()' calls to the alternative FTP native functions.
     *
     * @param string     $func
     * @param array|null $args
     *
     * @return mixed
     *
     * @throws WrapperException
     */
    public function __call(string $func, $args = null)
    {
        $function = "ftp_$func";

        if (!function_exists($function)) {
            throw new WrapperException("$function() doesn't exists.");
        }

        if (!in_array($func, ['connect', 'ssl_connect'])) {
            array_unshift($args, $this->connection->getStream());
        }

        set_error_handler(function () {
            $this->errorMessage = func_get_args()[1];
        });

        // clear the previous error message
        $this->errorMessage = '';

        try {
            return call_user_func_array($function, $args);
        } finally {
            restore_error_handler();
        }
    }
}
