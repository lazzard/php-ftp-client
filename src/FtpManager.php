<?php

namespace Lazzard\FtpClient;

use Lazzard\FtpClient\Command\FtpCommand;
use Lazzard\FtpClient\Configuration\Exception\FtpConfigurationLogicException;
use Lazzard\FtpClient\Configuration\Exception\FtpConfigurationRuntimeException;
use Lazzard\FtpClient\Configuration\FtpConfiguration;
use Lazzard\FtpClient\Configuration\ConfigurationInterface;
use Lazzard\FtpClient\Exception\FtpClientRuntimeException;

/**
 * Class FtpManager
 *
 * Manage FTP connection and setting FTP client configuration.
 *
 * @since 1.0
 * @package Lazzard\FtpClient
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
abstract class FtpManager
{
    /**
     * FTP predefined constants alias
     */
    const TIMEOUT_SEC    = FTP_TIMEOUT_SEC;
    const AUTOSEEK       = FTP_AUTOSEEK;
    const USEPASVADDRESS = FTP_USEPASVADDRESS;

    /** @var resource */
    protected $connection;

    /** @var \Lazzard\FtpClient\FtpWrapper */
    protected $ftpWrapper;

    /** @var \Lazzard\FtpClient\Configuration\FtpConfiguration */
    protected $ftpConfiguration;

    /** @var \Lazzard\FtpClient\Command\FtpCommand */
    protected $ftpCommand;

    /** @var string */
    protected $currentDir;

    /**
     * FtpManager constructor.
     *
     * @param \Lazzard\FtpClient\Configuration\ConfigurationInterface|null $ftpConfiguration
     */
    public function __construct(ConfigurationInterface $ftpConfiguration = null)
    {
        if (is_null($ftpConfiguration)) {
            $this->ftpConfiguration = new FtpConfiguration();
        } else {
            $this->ftpConfiguration = $ftpConfiguration;
        }

        $this->ftpWrapper = new FtpWrapper();
    }

    /**
     * Get current FTP stream resource.
     *
     * @return resource
     *
     * @throws \Lazzard\FtpClient\Exception\FtpClientRuntimeException
     */
    public function getConnection()
    {
        if (is_resource($this->connection))
            return $this->connection;

        throw new FtpClientRuntimeException("Invalid ftp resource stream, try to reconnect to the remote server.");
    }

    /**
     * @param resource $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return \Lazzard\FtpClient\FtpWrapper
     */
    protected function getFtpWrapper()
    {
        return $this->ftpWrapper;
    }

    /**
     * Get current FTP configuration.
     *
     * @return \Lazzard\FtpClient\Configuration\FtpConfiguration
     */
    public function getFtpConfiguration()
    {
        return $this->ftpConfiguration;
    }

    /**
     * @param \Lazzard\FtpClient\Configuration\FtpConfiguration $ftpConfiguration
     */
    public function setFtpConfiguration($ftpConfiguration)
    {
        $this->ftpConfiguration = $ftpConfiguration;
        $this->setClientConfiguration();
    }

    /**
     * @return string
     */
    public function getCurrentDir()
    {
        if (($currentDir = $this->getFtpWrapper()->pwd($this->getConnection())) !== '.')
            return $currentDir;

        return '';
    }

    /**
     * @param string $currentDir
     */
    public function setCurrentDir($currentDir)
    {
        $fixCurrentDir = $currentDir == '/' ? '' : $currentDir;
        
        if ($this->getFtpWrapper()->chdir($this->getConnection(), $fixCurrentDir) !==
        true)
            throw new FtpClientRuntimeException("Cannot change to the giving directory.");

        $this->currentDir = $currentDir;
    }

    /**
     * Set client ftp configuration.
     */
    private function setClientConfiguration()
    {
        $this->setOption(
            self::TIMEOUT_SEC,
            $this->getFtpConfiguration()->getTimeout()
        );

        $this->setOption(
            self::AUTOSEEK,
            $this->getFtpConfiguration()->isAutoSeek()
        );

        $this->setOption(
            self::USEPASVADDRESS,
            $this->getFtpConfiguration()->isUsePassiveAddress()
        );

        $this->setPassive($this->getFtpConfiguration()->isPassive());

        $this->setCurrentDir($this->getFtpConfiguration()->getRoot());
    }

    /**
     * Open an FTP connection.
     *
     * @param string $host    Host name
     * @param int    $port    Default sets to port 21
     * @param int    $timeout Default value is 90
     *
     * @return bool
     *
     */
    public function connect($host, $port = 21, $timeout = 90)
    {
        if (($connection = $this->getFtpWrapper()->connect(
            $host,
            $port,
            $timeout)) !== false)
        {
            $this->setConnection($connection);
            $this->ftpCommand = new FtpCommand($this->getConnection());
            return true;
        }

        throw new FtpClientRuntimeException("Connection failed to remote server.");
    }

    /**
     * Logging in to an FTP server.
     *
     * @param string $username
     * @param string $password
     *
     * @return bool
     *
     * @throws \Lazzard\FtpClient\Exception\FtpClientRuntimeException
     */
    public function login($username, $password)
    {
        if ($this->getFtpWrapper()->login($this->getConnection(), $username, $password) !== false) {
            $this->setClientConfiguration();
            return true;
        }

        throw new FtpClientRuntimeException("Logging failed to remote server.");
    }

    /**
     * Close an FTP connection.
     *
     * @return bool
     */
    public function close()
    {
        if ($this->getFtpWrapper()->close($this->getConnection()) !== true)
            throw new FtpClientRuntimeException("Failed to closing FTP connection.");

        return true;
    }

    /**
     * Set FTP runtime options.
     *
     * @param $option
     * @param $value
     *
     * @return bool
     */
    public function setOption($option, $value)
    {
        $options = [
          self::TIMEOUT_SEC,
          self::AUTOSEEK,
          self::USEPASVADDRESS
        ];

        if (in_array($option, $options) !== true) {
            throw new FtpConfigurationLogicException("{$option} is invalid FTP runtime option.");
        }

        if ($this->getFtpWrapper()->setOption($this->getConnection(), $option, $value) !== true) {
            throw new FtpClientRuntimeException("Unable to set FTP option.");
        }

        return true;
    }

    /**
     * Turn the passive mode on or off.
     *
     * Notice that the active mode is the default mode.
     *
     * @param $bool
     *
     * @return bool
     */
    public function setPassive($bool)
    {
        if ($this->getFtpWrapper()->pasv($this->getConnection(), $bool) !== true)
            throw new FtpConfigurationRuntimeException("Unable to switch FTP mode.");

        return true;
    }

    /**
     * Get supported remote server features.
     *
     * @return mixed
     *
     * @throws \Lazzard\FtpClient\Exception\FtpClientRuntimeException
     */
    public function getFeatures()
    {
        if ($this->ftpCommand->request("FEAT") !== false)
            return $this->ftpCommand->getResponse();

        throw new FtpClientRuntimeException("Cannot getting remote server features");
    }

}