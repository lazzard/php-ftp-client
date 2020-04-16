<?php

namespace Lazzard\FtpClient\Command;

use Lazzard\FtpClient\Command\Exception\FtpCommandRuntimeException;
use Lazzard\FtpClient\FtpWrapper;

/**
 * Class FtpCommand
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Command
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class FtpCommand implements CommandInterface
{
    /** @var resource */
    private $_connection;

    /** @var FtpWrapper */
    private $_ftpWrapper;

    /** @var mixed */
    private $_response;

    /** @var int */
    private $_responseCode;

    /** @var string */
    private $_responseMessage;

    /** @var string */
    private $_responseEndMessage;

    /** @var array */
    private $_responseBody;

    /**
     * FtpCommand constructor.
     *
     * @param $connection
     */
    public function __construct($connection)
    {
        $this->_connection = $connection;
        $this->_ftpWrapper = new FtpWrapper();
    }

    /**
     * @inheritDoc
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @inheritDoc
     */
    public function getResponseCode()
    {
        return $this->_responseCode;
    }

    /**
     * @inheritDoc
     */
    public function getResponseMessage()
    {
        return $this->_responseMessage;
    }

    /**
     * @return string
     */
    public function getResponseEndMessage()
    {
        return $this->_responseEndMessage;
    }

    /**
     * @inheritDoc
     */
    public function getResponseBody()
    {
        return $this->_responseBody;
    }

    /**
     * @return resource
     */
    private function getConnection()
    {
        return $this->_connection;
    }

    /**
     * @inheritDoc
     */
    public function isSucceeded()
    {
        return ($this->getResponseCode() >= 200 && $this->getResponseCode() <= 257);
    }

    /**
     * @return array
     */
    private function _supportedSiteCommands()
    {
        $commands = $this->_ftpWrapper->raw($this->getConnection(), 'HELP');

        return array_map(
            function ($item) {
                return ltrim(strtolower($item));
            },
            $commands
        );
    }

    /**
     * @return array
     */
    private function _features()
    {
        $commands = $this->_ftpWrapper->raw($this->getConnection(), 'FEAT');

        return array_map(
            function ($item) {
                return ltrim(strtolower($item));
            },
            $commands
        );
    }

    /**
     * Sets an FtpClient response (not a server response).
     *
     * @param $responseCode
     * @param $responseMessage
     */
    private function _responseFormatter($responseCode, $responseMessage)
    {
        $this->_responseCode    = $responseCode;
        $this->_responseMessage = $responseMessage;
        $this->_response = sprintf(
            "%s - %s",
            $responseCode,
            $responseMessage
        );
    }

    /**
     * @inheritDoc
     */
    public function rawRequest($command)
    {
        $this->_response        = $this->_ftpWrapper->raw($this->getConnection(), trim($command));
        $this->_responseCode    = intval(substr($this->getResponse()[0], 0, 3));
        $this->_responseMessage = ltrim(substr($this->getResponse()[0], 3));

        if ($this->isSucceeded()) {
            $response = $this->getResponse();
            $responseBody = array_splice($response, 1, -1);
            $this->_responseBody = $responseBody ?: null;

            if (count($this->getResponse()) > 1) {
                $this->_responseEndMessage = $this->getResponse()[count($this->getResponse()) - 1];
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function siteRequest($command)
    {
        $siteCommand = strtolower(explode(' ', trim($command))[0]);

        if (!in_array($siteCommand, $this->_supportedSiteCommands())) {
            throw new FtpCommandRuntimeException("{$siteCommand} SITE command not supported by the remote server.");
        }

        if ($this->_ftpWrapper->site($this->getConnection(), trim($command)) !== true) {
            $this->_responseFormatter(500, '[FtpClient] SITE command was failed.');
        } else {
            $this->_responseFormatter(200, '[FtpClient] SITE command succeeded.');
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function execRequest($command)
    {
        if (!in_array('exec', $this->_supportedSiteCommands())) {
            throw new FtpCommandRuntimeException("SITE EXEC command not provided by the FTP server.");
        }

        if (($this->_ftpWrapper->exec($this->getConnection(), $command)) !== true) {
            $this->_responseFormatter(500, '[FtpClient] SITE EXEC command was failed.');
        } else {
            $this->_responseFormatter(200, '[FtpClient] SITE EXEC command succeeded.');
        }

        return $this;
    }
}