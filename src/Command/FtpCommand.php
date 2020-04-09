<?php

namespace Lazzard\FtpClient\Command;

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
     * @inheritDoc
     */
    public function request($command)
    {
        $this->setResponse($this->getFtpWrapper()->raw($this->getConnection(), trim($command)));
        $this->setResponseCode(intval(substr($this->getResponse()[0], 0, 3)));
        $this->setResponseMessage(ltrim(substr($this->getResponse()[0], 3)));

        $response = $this->getResponse();
        $responseBody = array_splice($response, 1, -1);
        $this->setResponseBody($responseBody ?: null);

        if ($this->getResponseCode() === 500)
            return false;

        return true;
    }

}