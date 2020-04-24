<?php

namespace Lazzard\FtpClient\Exception;

/**
 * Class CommandException
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Exception
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class CommandException extends ClientException
{
    public function __construct($message)
    {
        parent::__construct("[CommandException] - " . $message);
    }
}