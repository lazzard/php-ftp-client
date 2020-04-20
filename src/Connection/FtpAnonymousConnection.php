<?php


namespace Lazzard\FtpClient\Connection;


use Lazzard\FtpClient\Exception\ConnectionException;

class FtpAnonymousConnection extends FtpConnection
{

    public function __construct($host, $username = "anonymous", $password = "guest", $port = 21, $timeout = 90)
    {
        parent::__construct($host, $username, $password, $port, $timeout);
    }

    protected function _login()
    {
        if ( ! ($connection = @ftp_login($this->getStream(), $this->getUsername(),
            $this->getPassword()))) {
            throw new ConnectionException("Could not logging into the remote server, may be the your FTP server not support anonymous FTP.");
        }

        return $connection;
    }

}