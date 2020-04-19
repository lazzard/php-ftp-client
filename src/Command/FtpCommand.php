<?php

namespace Lazzard\FtpClient\Command;

use Lazzard\FtpClient\Exception\CommandException;
use Lazzard\FtpClient\FtpWrapper;

/**
 * Class CommandException
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Command
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class FtpCommand implements CommandInterface
{
    /** @var resource */
    private $connection;

    /** @var FtpWrapper */
    private $ftpWrapper;

    /** @var mixed */
    private $response;

    /** @var int */
    private $responseCode;

    /** @var string */
    private $responseMessage;

    /** @var string */
    private $responseEndMessage;

    /** @var array */
    private $responseBody;

    /**
     * CommandException constructor.
     *
     * @param $connection
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->ftpWrapper = new FtpWrapper();
    }

    /**
     * @inheritDoc
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @inheritDoc
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @inheritDoc
     */
    public function getResponseMessage()
    {
        return $this->responseMessage;
    }

    /**
     * @return string
     */
    public function getResponseEndMessage()
    {
        return $this->responseEndMessage;
    }

    /**
     * @inheritDoc
     */
    public function getResponseBody()
    {
        return $this->responseBody;
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
        $commands = $this->ftpWrapper->raw($this->connection, 'HELP');

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
     * This method sets an FtpClient response, as
     * an attempt to cover the boolean returns values of
     * the The ftp_site and ftp_exec functions.
     *
     * @param $responseCode
     * @param $responseMessage
     */
    private function _responseFormatter($responseCode, $responseMessage)
    {
        $this->responseCode    = $responseCode;
        $this->responseMessage = $responseMessage;
        $this->response = sprintf(
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
        $this->response        = $this->ftpWrapper->raw($this->connection, trim($command));
        $this->responseCode    = intval(substr($this->getResponse()[0], 0, 3));
        $this->responseMessage = ltrim(substr($this->getResponse()[0], 3));

        if ($this->isSucceeded()) {
            $response = $this->getResponse();
            $responseBody = array_splice($response, 1, -1);
            $this->responseBody = $responseBody ?: null;

            if (count($this->getResponse()) > 1) {
                $this->responseEndMessage = $this->getResponse()[count($this->getResponse()) - 1];
            }
        }

        return $this;
    }

    /**
     * @see FtpCommand::_responseFormatter()
     *
     * {@inheritDoc}
     */
    public function siteRequest($command)
    {
        $siteCommand = strtolower(explode(' ', trim($command))[0]);

        if ( ! in_array($siteCommand, $this->_supportedSiteCommands())) {
            throw new CommandException("{$siteCommand} SITE command not supported by the remote server.");
        }

        if ($this->ftpWrapper->site($this->connection, trim($command)) !== true) {
            $this->_responseFormatter(500, '[FtpClient] SITE command was failed.');
        } else {
            $this->_responseFormatter(200, '[FtpClient] SITE command succeeded.');
        }

        return $this;
    }

    /**
     * @see FtpCommand::_responseFormatter()
     *
     * {@inheritDoc}
     */
    public function execRequest($command)
    {
        if ( ! in_array('exec', $this->_supportedSiteCommands())) {
            throw new CommandException("SITE EXEC command not provided by the FTP server.");
        }

        if ($this->ftpWrapper->exec($this->connection, $command) !== true) {
            $this->_responseFormatter(500, '[FtpClient] SITE EXEC command was failed.');
        } else {
            $this->_responseFormatter(200, '[FtpClient] SITE EXEC command succeeded.');
        }

        return $this;
    }
}