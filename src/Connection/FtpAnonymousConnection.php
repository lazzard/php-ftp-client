<?php


namespace Lazzard\FtpClient\Connection;

use Lazzard\FtpClient\Exception\ConnectionException;

/**
 * Class FtpAnonymousConnection represents an FTP anonymous connection.
 *
 * Note : not all FTP servers support this kind of connections.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpAnonymousConnection extends FtpConnection
{

    /**
     * FtpAnonymousConnection constructor.
     *
     * @param string $host
     * @param string $username [optional]
     * @param string $password [optional]
     * @param int    $port     [optional]
     * @param int    $timeout  [optional]
     */
    public function __construct($host, $username = "anonymous", $password = "guest", $port = 21, $timeout = 90)
    {
        parent::__construct($host, $username, $password, $port, $timeout);
    }

    /**
     * @inheritDoc
     */
    protected function login()
    {
        if ( ! ($connection = $this->wrapper->login($this->getUsername(),
            $this->getPassword()))
        ) {
            throw new ConnectionException(
                "Could not logging into the remote server, may be your FTP server not support anonymous FTP."
            );
        }

        return $connection;
    }

}