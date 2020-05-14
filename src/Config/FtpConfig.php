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

use Lazzard\FtpClient\Connection\ConnectionInterface;
use Lazzard\FtpClient\Exception\ConfigException;
use Lazzard\FtpClient\FtpWrapper;

/**
 * Simple class to manage an FTP connection.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
final class FtpConfig
{
    /** @var FtpWrapper */
    private $wrapper;

    /** @var array */
    private $config = [
        'passive'           => false,
        'timeout'           => 90,
        'autoSeek'          => true,
        'usePassiveAddress' => true,
        'initialDirectory'  => '/'
    ];

    /**
     * FtpConfig constructor.
     *
     * @param ConnectionInterface $connection
     * @param array|null          $config [optional]
     */
    public function __construct($connection, $config = null)
    {
        if ($config) {
            $this->config = array_merge($config, $config);
        }

        $this->wrapper = new FtpWrapper($connection);
    }

    /**
     * Gets configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Sets client's configuration.
     *
     * @throws ConfigException
     */
    public function apply()
    {
        $this->setPassive($this->config['passive']);
        $this->setTimeout($this->config['timeout']);
        $this->setAutoSeek($this->config['autoSeek']);
        $this->usePassiveAddress($this->config['usePassiveAddress']);
        $this->wrapper->chdir($this->config['initialDirectory']);
    }

    /**
     * Sets the provided INI directives values.
     *
     * @param array $iniConfig
     *
     * @throws ConfigException
     */
    public function setPhpLimit($iniConfig)
    {
        $config = [
            'maxExecutionTime' => null,
            'ignoreUserAbort'  => null,
            'memoryLimit'      => null
        ];

        $config = array_merge($config, $iniConfig);

        if (($value = $config['maxExecutionTime']) !== null) {
            if ( ! set_time_limit($value)) {
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
     * Turn the passive mode on or off.
     *
     * @param $bool
     *
     * @return bool
     *
     * @throws ConfigException
     */
    public function setPassive($bool)
    {
        if ( ! $this->wrapper->pasv($bool)) {
            throw new ConfigException(ConfigException::getFtpServerError()
                ?: "Unable to switch FTP mode."
            );
        }

        return true;
    }

    /**
     * Sets the timeout in seconds for all FTP network operations.
     *
     * @param int $value
     *
     * @return bool
     *
     * @throws ConfigException
     */
    public function setTimeout($value)
    {
        if ( ! is_int($value) || $value < 0) {
            throw new ConfigException(
                "[{$value}] Timeout option value must be of type integer and greater than 0."
            );
        }

        if ( ! $this->wrapper->setOption(FtpWrapper::TIMEOUT_SEC, $value)) {
            throw new ConfigException(ConfigException::getFtpServerError()
                ?: "Unable to set Timeout runtime option."
            );
        }

        return true;
    }

    /**
     * Sets the autoSeek option on/off.
     *
     * @param bool $value
     *
     * @return bool
     *
     * @throws ConfigException
     */
    public function setAutoSeek($value)
    {
        if ( ! is_bool($value)) {
            throw new ConfigException(
                "[{$value}] AutoSeek option value must be of type boolean."
            );
        }

        if ( ! $this->wrapper->setOption(FtpWrapper::AUTOSEEK, $value)) {
            throw new ConfigException(ConfigException::getFtpServerError()
                ?: "Unable to set AutoSeek runtime option."
            );
        }

        return true;
    }

    /**
     * Specifies if the IP address returned via the PASV command
     * will be used to open the data channel.
     *
     * @param bool $value
     *
     * @return bool
     *
     * @throws ConfigException
     */
    public function usePassiveAddress($value)
    {
        if ( ! is_bool($value)) {
            throw new ConfigException(
                "[{$value}] usePassiveAddress option value must be of type boolean."
            );
        }

        if ( ! $this->wrapper->setOption(FtpWrapper::USEPASVADDRESS, $value)) {
            throw new ConfigException(ConfigException::getFtpServerError()
                ?: "Unable to set usePassiveAddress runtime option."
            );
        }

        return true;
    }

    /**
     * Gets the timeout option value.
     *
     * @return int
     *
     * @throws ConfigException
     */
    public function getTimeout()
    {
        if ( ! ($optionValue = $this->wrapper->getOption(FtpWrapper::TIMEOUT_SEC))) {
            throw new ConfigException(ConfigException::getFtpServerError()
                ?: "Unable to get FTP timeout option value."
            );
        }

        return $optionValue;
    }

    /**
     * Checks if the autoSeek option enabled or not.
     *
     * @return bool
     *
     * @throws ConfigException
     */
    public function isAutoSeek()
    {
        if ( ! ($optionValue = $this->wrapper->getOption(FtpWrapper::AUTOSEEK))) {
            throw new ConfigException(ConfigException::getFtpServerError()
                ?: "Unable to get FTP timeout option value."
            );
        }

        return $optionValue;
    }

    /**
     * Checks if the passive address returned in the PASV response
     * is used by the control channel or not.
     *
     * @return bool
     *
     * @throws ConfigException
     */
    public function isUsePassiveAddress()
    {
        if ( ! ($optionValue = $this->wrapper->getOption(FtpWrapper::AUTOSEEK))) {
            throw new ConfigException(ConfigException::getFtpServerError()
                ?: "Unable to get FTP timeout option value."
            );
        }

        return $optionValue;
    }
}