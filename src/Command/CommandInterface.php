<?php


namespace Lazzard\FtpClient\Command;

use Lazzard\FtpClient\Command\Exception\FtpCommandRuntimeException;

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
     * Sends an arbitrary command to the remote server.
     *
     * @param string $command
     *
     * @return FtpCommand Return FtpCommand Instance
     */
    public function rawRequest($command);

    /**
     * Sends a SITE command to the FTP server.
     *
     * @param string $command
     *
     * @return FtpCommand Return FtpCommand Instance
     *
     * @throws FtpCommandRuntimeException
     */
    public function siteRequest($command);

    /**
     * Send a SITE EXEC command to the remote server.
     *
     * @param string $command
     *
     * @return FtpCommand Return FtpCommand Instance
     *
     * @throws FtpCommandRuntimeException
     */
    public function execRequest($command);

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
     * Get server the end status response message for the previous (raw command) request.
     *
     * @return string|null
     */
    public function getEndResponseMessage();

    /**
     * Get server response body for the previous (raw command) request.
     *
     * @return array|null
     */
    public function getResponseBody();

    /**
     * Check weather if the previous command request was succeeded or not.
     * 
     * @return bool
     */
    public function isSucceeded();
}