<?php


namespace Lazzard\FtpClient\Command;


use Lazzard\FtpClient\FtpWrapper;

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
     * @return resource
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return \Lazzard\FtpClient\FtpWrapper
     */
    public function getFtpWrapper()
    {
        return $this->ftpWrapper;
    }

    /**
     * @inheritDoc
     */
    public function request($command)
    {
        $this->setResponse($this->getFtpWrapper()->raw($this->getConnection(), trim($command)));

        if (is_array($this->getResponse())) {
            $this->setResponseCode(intval(substr($this->getResponse()[0], 0, 3)));
        } else {
            $this->setResponseCode(intval(substr($this->getResponse(), 0, 3)));
        }

        if ($this->getResponse() === 500)
            return false;

        return true;
    }

}