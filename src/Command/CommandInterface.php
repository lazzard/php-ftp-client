<?php


namespace Lazzard\FtpClient\Command;

/**
 * Interface CommandInterface
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Command
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
interface CommandInterface
{
    /**
     * Send an arbitrary command to the remote server.
     *
     * @param $command
     *
     * @return bool Return true if the command success (server response code lesser than 300),
     *              otherwise return false.
     */
    public function rawRequest($command);

    /**
     * Send a SITE command to the FTP server.
     *
     * @param $command
     *
     * @return bool Return true in success, throws exception in failure.
     *
     * @see \Lazzard\FtpClient\Command\FtpCommand::rawRequest()
     *
     * @throws \Lazzard\FtpClient\Command\Exception\FtpCommandException
     */
    public function siteRequest($command);

    /**
     * Get server response for the previous command request.
     *
     * @return mixed
     */
    public function getResponse();

    /**
     * Get server response status code for the previous command request.
     *
     * @return int
     */
    public function getResponseCode();

    /**
     * Get server response status message for the previous command request.
     *
     * @return string
     */
    public function getResponseMessage();

    /**
     * Get server the end status response message for the previous command request.
     *
     * @return string
     */
    public function getEndResponseMessage();

    /**
     * Get server response body for the previous command request.
     *
     * @return array|null
     */
    public function getResponseBody();
}