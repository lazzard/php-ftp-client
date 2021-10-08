<?php declare(strict_types=1);

/**
 * This file is part of the Lazzard/php-ftp-client package.
 *
 * (c) El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lazzard\FtpClient\Exception;

/**
 * @since 1.3.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * @internal
 */
class WrapperException extends FtpClientException
{
    public function __construct(string $message)
    {
        parent::__construct("[WrapperException] - $message");
    }
}
