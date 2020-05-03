<?php

namespace Lazzard\FtpClient\Exception;

/**
 * Class ConnectionException
 *
 * @since 1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class ConnectionException extends ClientException
{
    public function __construct($message)
    {
        parent::__construct("[ConnectionException] - " . $message);
    }
}