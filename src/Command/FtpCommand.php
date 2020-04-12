<?php

namespace Lazzard\FtpClient\Command;

use Lazzard\FtpClient\Command\Exception\FtpCommandException;
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

    /** @var \Lazzard\FtpClient\FtpWrapper */
    private $ftpWrapper;

    /** @var mixed */
    private $response;

    /** @var int */
    private $responseCode;

    /** @var string */
    private $responseMessage;

    /** @var string */
    private $endResponseMessage;

    /** @var mixed */
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
     * @return \Lazzard\FtpClient\FtpWrapper
     */
    private function getFtpWrapper()
    {
        return $this->ftpWrapper;
    }

    /**
     * @return array
     */
    private function supportedSiteCommands()
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
     * @inheritDoc
     */
    public function rawRequest($command)
    {
        $this->setResponse($this->getFtpWrapper()->raw($this->getConnection(), trim($command)));
        $this->setResponseCode(intval(substr($this->getResponse()[0], 0, 3)));
        $this->setResponseMessage(ltrim(substr($this->getResponse()[0], 3)));

        if ($this->getResponseCode() < 300) {

            $response = $this->getResponse();
            $responseBody = array_splice($response, 1, -1);
            $this->setResponseBody($responseBody ?: null);

            if (count($this->getResponse()) > 1) {
                $this->setEndResponseMessage($this->getResponse()[count($this->getResponse()) - 1]);
            }
        }

        return ($this->getResponseCode() < 300);
    }


    /**
     * @inheritDoc
     */
    public function siteRequest($command)
    {
        $siteCommand = strtolower(explode(' ', trim($command))[0]);

        if (in_array($siteCommand, $this->supportedSiteCommands()) !== true) {
            throw new FtpCommandException("{$siteCommand} command not supported by the remote server.");
        }

        if ($this->getFtpWrapper()->site($this->getConnection(), trim($command)) !== true) {
            throw new FtpCommandException("SITE command was fail.");
        }

        $this->setResponseCode(200);
        $this->setResponseMessage("[FtpClient] SITE command succeeded.");
        $this->setResponse(sprintf(
            "%s - %s",
            $this->getResponseCode(),
            $this->getResponseMessage()
        ));

        return true;
    }

    /**
     * @inheritDoc
     */
    public function execRequest($command)
    {
        if (in_array('exec', $this->supportedSiteCommands()) !== true) {
            throw new FtpCommandException("SITE EXEC command not provided by the FTP server.");
        }

        if (($this->getFtpWrapper()->exec($this->getConnection(), $command)) !== true) {
            throw new FtpCommandException("SITE EXEC command was fail.");
        }

        $this->setResponseCode(200);
        $this->setResponseMessage("[FtpClient] SITE EXEC command succeeded.");
        $this->setResponse(sprintf(
            "%s - %s",
            $this->getResponseCode(),
            $this->getResponseMessage()
        ));

        return true;
    }

}