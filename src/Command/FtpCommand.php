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
    private $endResponseMessage;

    /** @var array */
    private $responseBody;

    /**
     * FtpCommand constructor.
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
     * @param mixed $response
     */
    private function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @inheritDoc
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @param int $responseCode
     */
    private function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;
    }

    /**
     * @inheritDoc
     */
    public function getResponseMessage()
    {
        return $this->responseMessage;
    }

    /**
     * @param string $responseMessage
     */
    private function setResponseMessage($responseMessage)
    {
        $this->responseMessage = $responseMessage;
    }

    /**
     * @return string
     */
    public function getEndResponseMessage()
    {
        return $this->endResponseMessage;
    }

    /**
     * @param string $endResponseMessage
     */
    private function setEndResponseMessage($endResponseMessage)
    {
        $this->endResponseMessage = $endResponseMessage;
    }

    /**
     * @inheritDoc
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }

    /**
     * @param mixed $responseBody
     */
    private function setResponseBody($responseBody)
    {
        $this->responseBody = $responseBody;
    }

    /**
     * @return resource
     */
    private function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return FtpWrapper
     */
    private function getFtpWrapper()
    {
        return $this->ftpWrapper;
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

    protected function _setResponse($responseCode, $responseMessage)
    {
        $this->setResponseCode($responseCode);
        $this->setResponseMessage($responseMessage);
        $this->setResponse(sprintf(
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
        $this->setResponse($this->getFtpWrapper()->raw($this->getConnection(), trim($command)));
        $this->setResponseCode(intval(substr($this->getResponse()[0], 0, 3)));
        $this->setResponseMessage(ltrim(substr($this->getResponse()[0], 3)));

        if ($this->getResponseCode() <= 257 && $this->getResponseCode() >= 200) {

            $response = $this->getResponse();
            $responseBody = array_splice($response, 1, -1);
            $this->setResponseBody($responseBody ?: null);

            if (count($this->getResponse()) > 1) {
                $this->setEndResponseMessage($this->getResponse()[count($this->getResponse()) - 1]);
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
            $this->_setResponse(500, '[FtpClient] SITE command was failed.');
        } else {
            $this->_setResponse(200, '[FtpClient] SITE command succeeded.');
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
            $this->_setResponse(500, '[FtpClient] SITE EXEC command was failed.');
        } else {
            $this->_setResponse(200, '[FtpClient] SITE EXEC command succeeded.');
        }

        return $this;
    }

}