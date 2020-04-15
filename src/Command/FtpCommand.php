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
    private $_endResponseMessage;

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
     * @param mixed $response
     */
    private function _setResponse($response)
    {
        $this->_response = $response;
    }

    /**
     * @inheritDoc
     */
    public function getResponseCode()
    {
        return $this->_responseCode;
    }

    /**
     * @param int $responseCode
     */
    private function _setResponseCode($responseCode)
    {
        $this->_responseCode = $responseCode;
    }

    /**
     * @inheritDoc
     */
    public function getResponseMessage()
    {
        return $this->_responseMessage;
    }

    /**
     * @param string $responseMessage
     */
    private function _setResponseMessage($responseMessage)
    {
        $this->_responseMessage = $responseMessage;
    }

    /**
     * @return string
     */
    public function getEndResponseMessage()
    {
        return $this->_endResponseMessage;
    }

    /**
     * @param string $endResponseMessage
     */
    private function _setEndResponseMessage($endResponseMessage)
    {
        $this->_endResponseMessage = $endResponseMessage;
    }

    /**
     * @inheritDoc
     */
    public function getResponseBody()
    {
        return $this->_responseBody;
    }

    /**
     * @param mixed $responseBody
     */
    private function _setResponseBody($responseBody)
    {
        $this->_responseBody = $responseBody;
    }

    /**
     * @return resource
     */
    private function getConnection()
    {
        return $this->_connection;
    }

    /**
     * @return FtpWrapper
     */
    private function getFtpWrapper()
    {
        return $this->_ftpWrapper;
    }

    /**
     * @inheritDoc
     */
    public function isSucceeded()
    {
        return $this->getResponseCode() >= 200 && $this->getResponseCode() <= 257;
    }

    /**
     * @return array
     */
    private function _supportedSiteCommands()
    {
        $this->rawRequest("HELP");

        return array_map(
            function ($item) {
                return ltrim(strtolower($item));
            },
            $this->getResponseBody()
        );
    }

    /**
     * Sets response status code and response status message,
     * And Formatting the response string.
     *
     * @param $responseCode
     * @param $responseMessage
     */
    private function _responseFormatter($responseCode, $responseMessage)
    {
        $this->_setResponseCode($responseCode);
        $this->_setResponseMessage($responseMessage);
        $this->_setResponse(sprintf(
            "%s - %s",
            $responseCode,
            $responseMessage
        ));
    }

    /**
     * @inheritDoc
     */
    public function rawRequest($command)
    {
        $this->_setResponse($this->getFtpWrapper()->raw($this->getConnection(), trim($command)));
        $this->_setResponseCode(intval(substr($this->getResponse()[0], 0, 3)));
        $this->_setResponseMessage(ltrim(substr($this->getResponse()[0], 3)));

        if ($this->getResponseCode() >= 200 && $this->getResponseCode() <= 257) {

            $response = $this->getResponse();
            $responseBody = array_splice($response, 1, -1);
            $this->_setResponseBody($responseBody ?: null);

            if (count($this->getResponse()) > 1) {
                $this->_setEndResponseMessage($this->getResponse()[count($this->getResponse()) - 1]);
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

        if (in_array($siteCommand, $this->_supportedSiteCommands()) !== true) {
            throw new FtpCommandRuntimeException("{$siteCommand} SITE command not supported by the remote server.");
        }

        if ($this->getFtpWrapper()->site($this->getConnection(), trim($command)) !== true) {
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
        if (in_array('exec', $this->_supportedSiteCommands()) !== true) {
            throw new FtpCommandRuntimeException("SITE EXEC command not provided by the FTP server.");
        }

        if (($this->getFtpWrapper()->exec($this->getConnection(), $command)) !== true) {
            $this->_responseFormatter(500, '[FtpClient] SITE EXEC command was failed.');
        } else {
            $this->_responseFormatter(200, '[FtpClient] SITE EXEC command succeeded.');
        }

        return $this;
    }

}