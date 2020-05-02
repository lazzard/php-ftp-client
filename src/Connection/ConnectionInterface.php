<?php

namespace Lazzard\FtpClient\Connection;

use Lazzard\FtpClient\Exception\ConnectionException;

/**
 * Interface that all FTP connections classes must implements.
 *
 * @since 1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
interface ConnectionInterface
{
    /**
     * Opens an FTP connection.
     *
     * @return bool
     *
     * @throws ConnectionException
     */
    public function open();

    /**
     * Close the FTP connection.
     *
     * @return bool
     *
     * @throws ConnectionException
     */
    public function close();

    /**
     * @return resource
     *
     * @throws ConnectionException
     */
    public function getStream();

    /**
     * @return string
     */
    public function getHost();

    /**
     * @return int
     */
    public function getPort();

    /**
     * @return int
     */
    public function getTimeout();

}