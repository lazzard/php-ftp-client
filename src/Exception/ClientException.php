<?php

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
 * Class ClientException
 *
 * @since 1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class ClientException extends \Exception implements FtpClientException
{
    public function __construct($message)
    {
        parent::__construct(
            self::classBaseName(get_class($this)) === self::classBaseName(self::class)
            ? "[FtpClientException] - " . $message : $message
        );
    }

    public static function getFtpServerError()
    {
        return @explode(' ', error_get_last()['message'], 2)[1];
    }

    private static function classBaseName($class)
    {
        return basename(str_replace('\\', '/', $class), $class);
    }
}
