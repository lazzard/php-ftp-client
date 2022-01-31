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
     */
    public function open() : bool;

    /**
     * Closes the FTP connection.
     */
    public function close() : bool;

    /**
     * @return resource
     */
    public function getStream();

    public function getHost() : string;

    public function getPort() : int;

    public function getTimeout() : int;

    public function getUsername() : string;

    public function getPassword() : string;

    public function isConnected() : bool;
}
