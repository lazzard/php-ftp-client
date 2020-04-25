<?php

namespace Lazzard\FtpClient\Configuration;

use Lazzard\FtpClient\Exception\ConfigurationException;

/**
 * Class PhpIniConfig
 *
 * @since 1.0
 * @package Lazzard\FtpClient\FtpConfiguration
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class PhpIniConfig extends FileConfiguration
{
    /** @var array */
    protected $config;

    /**
     * PhpIniConfig constructor.
     *
     * @param array|null $config
     */
    public function __construct($config = null)
    {
        parent::__construct();

        $this->setConfig($config);
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfig($config)
    {
        $this->config = $config
            ? array_merge(self::$configFile['phpLimit'], $config)
            : self::$configFile['phpLimit'];
    }

    /**
     * Sets the php limit configuration options specified in the config file.
     * 
     * @throws ConfigurationException
     */
    public function apply()
    {
        if ($this->config['maxExecutionTime'] !== NOT_CHANGE ) {
            if ( ! set_time_limit($this->config['maxExecutionTime'] === UNLIMITED ? 0 : $this->config['maxExecutionTime'])) {
                throw new ConfigurationException("Failed to set max_execution_time directive value.");
            }
        }

        if ($this->config['ignoreUserAbort'] !== NOT_CHANGE) {
            ignore_user_abort($this->config['ignoreUserAbort']);
            if ((bool)ini_get('ignore_user_abort') !== $this->config['ignoreUserAbort']) {
                throw new ConfigurationException("Unable to set ignore_user_abort directive value.");
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function _validateTypeConstraints()
    {

    }

}