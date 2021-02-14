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

/**
 * An interface that's all FTP connection classes must implements.
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
     */
    public function open();

    /**
     * Closes an FTP connection.
     *
     * @return bool
     */
    public function close();

    /**
     * @return resource
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

    /**
     * @return string
     */
    public function getUsername();

    /**
     * @return string
     */
    public function getPassword();
}
