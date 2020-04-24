<?php

namespace Lazzard\FtpClient\Command;

use Lazzard\FtpClient\Connection\FtpConnection;
use Lazzard\FtpClient\Connection\ConnectionInterface;
use Lazzard\FtpClient\Exception\CommandException;
use Lazzard\FtpClient\FtpWrapper;

/**
 * Class CommandException
 *
 * @since 1.0
 * @package Lazzard\FtpClient\FtpCommand
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class FtpCommand
{
    /** @var FtpConnection */
    private $connection;

    /** @var FtpWrapper */
    private $wrapper;

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
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
        $this->wrapper    = new FtpWrapper($connection);
    }

    /**
     * @return FtpConnection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get server response for the previous command request.
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get server response status code for the previous command request.
     *
     * @return int
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * Get server response status message for the previous command request.
     *
     * @return string
     */
    public function getResponseMessage()
    {
        return $this->responseMessage;
    }

    /**
     * Get server the end status response message for the previous (raw command) request.
     *
     * @return string|null
     */
    public function getResponseEndMessage()
    {
        return $this->responseEndMessage;
    }

    /**
     * Get server response body for the previous (raw command) request.
     *
     * @return array|null
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }

    /**
     * Check weather if the previous command request was succeeded or not.
     *
     * @return bool
     */
    public function isSucceeded()
    {
        return ($this->responseCode >= 200 && $this->responseCode <= 257);
    }

    /**
     * Send a request to FTP server for execution an arbitrary command.
     *
     * @see FtpCommand::isSucceeded()
     *
     * @param string $command
     *
     * @return FtpCommand Return $this
     */
    public function rawRequest($command)
    {
        $this->response = $this->wrapper->raw(trim($command));
        $this->responseCode = intval(substr($this->response[0], 0, 3));
        $this->responseMessage = ltrim(substr($this->response[0], 3));

        if ($this->isSucceeded()) {
            $this->responseBody = array_slice($this->response, 1, -1) ?: null;

            if (count($this->response) > 1) {
                $this->responseEndMessage = $this->response[count($this->response) - 1];
            }
        }

        return $this;
    }

    /**
     * Send a request to FTP server for execution a SITE command.
     *
     * @see FtpCommand::_supportedSiteCommands()
     *
     * @param string $command
     *
     * @return FtpCommand Return $this
     *
     * @throws CommandException
     */
    public function siteRequest($command)
    {
        $siteCommand = strtolower(explode(' ', trim($command))[0]);

        if ( ! in_array($siteCommand, $this->_supportedSiteCommands())) {
            throw new CommandException("{$siteCommand} SITE command not supported by the remote server.");
        }

        if ( ! $this->wrapper->site(trim($command))) {
            $this->_responseFormatter(500, '[FtpClient] SITE command was failed.');
        } else {
            $this->_responseFormatter(200, '[FtpClient] SITE command succeeded.');
        }

        return $this;
    }

    /**
     * Send a request to FTP server for execution a SITE EXEC command.
     *
     * @see FtpCommand::_supportedSiteCommands()
     *
     * @param string $command
     *
     * @return FtpCommand Return $this
     *
     * @throws CommandException
     */
    public function execRequest($command)
    {
        if ( ! in_array('exec', $this->_supportedSiteCommands())) {
            throw new CommandException("SITE EXEC command not provided by the FTP server.");
        }

        if ( ! $this->wrapper->exec(trim($command))) {
            $this->_responseFormatter(500, '[FtpClient] SITE EXEC command was failed.');
        } else {
            $this->_responseFormatter(200, '[FtpClient] SITE EXEC command succeeded.');
        }

        return $this;
    }

    /**
     * @param string     $command
     * @param array|null $options[optional]
     *
     * @return string
     */
    public function prepareCommand($command, $options = null)
    {
        $command = explode(" ", trim($command));

        $fileName = @$command[1];

        $command = $command[0];

        if ( ! is_null($options)) {
            foreach ($options as $op) {
                $command .= ' ' . $op;
            }
        }

        return rtrim(sprintf("%s %s", $command, $fileName));
    }

    /**
     * @return array
     */
    private function _supportedSiteCommands()
    {
        return array_map(
            function ($item) {
                return ltrim(strtolower($item));
            },
            $this->rawRequest("SITE HELP")->getResponseBody()
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

}