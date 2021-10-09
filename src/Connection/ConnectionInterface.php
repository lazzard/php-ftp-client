<?php declare(strict_types=1);

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
     * Opens the FTP connection.
     *
     * @return bool
     */
    public function open() : bool;

    /**
     * Closes the FTP connection.
     *
     * @return bool
     */
    public function close() : bool;

    /**
     * @return resource
     */
    public function getStream();

    /**
     * @return string
     */
    public function getHost() : string;

    /**
     * @return int
     */
    public function getPort() : int;

    /**
     * @return int
     */
    public function getTimeout() : int;

    /**
     * @return string
     */
    public function getUsername() : string;

    /**
     * @return string
     */
    public function getPassword() : string;

    /**
     * @return bool
     */
    public function isConnected() : bool;
}
