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
 * FtpClientException is a generic exception class for the library.
 * 
 * @since 1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpClientException extends \Exception
{
    public function __construct(string $message)
    {
        if (preg_match('/(\[\w+]\s-)/', $message) === 0) {
            $message = "[FtpClientException] - $message";
        }

        parent::__construct($message);
    }
}
