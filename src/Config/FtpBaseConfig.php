<?php

/**
 * This file is part of the Lazzard/php-ftp-client package.
 *
 * (c) El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lazzard\FtpClient\Config;

use Lazzard\FtpClient\Exception\ConfigException;

/**
 * FtpBaseConfig class
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
final class FtpBaseConfig
{
    /**
     * Sets the client's INI configuration.
     *
     * @param array $iniConfig
     *
     * @throws ConfigException
     *
     * @return void
     */
    public static function setPhpLimit($iniConfig)
    {
        $config = [
            'maxExecutionTime' => null,
            'ignoreUserAbort'  => null,
            'memoryLimit'      => null
        ];

        foreach ($iniConfig as $name => $value) {
            if (!array_key_exists($name, $config)) {
                throw new ConfigException("[{$name}] is invalid option.");
            }
        }

        $config = array_merge($config, $iniConfig);

        if (($value = $config['maxExecutionTime']) !== null) {
            if (!set_time_limit($value)) {
                throw new ConfigException("Failed to set max_execution_time value to [{$value}].");
            }
        }

        if (($value = $config['ignoreUserAbort']) !== null) {
            ignore_user_abort($value);

            if ((bool)ini_get('ignore_user_abort') !== $value) {
                throw new ConfigException("Unable to set ignore_user_abort value to [{$value}].");
            }
        }

        if (($value = $config['memoryLimit']) !== null) {
            ini_set('memory_limit', sprintf('%sM', $value));

            if ((int)ini_get('memory_limit') !== $value) {
                throw new ConfigException("Failed to set memory_limit value to [{$value}M].");
            }
        }
    }

    /**
     * Checks if FTP extension is loaded, if not an exception throws.
     *
     * @throws ConfigException
     *
     * @return void
     */
    public static function isFtpExtensionLoaded()
    {
        if (!in_array('ftp', get_loaded_extensions())) {
            throw new ConfigException("Extension not loaded");
        }
    }
}
