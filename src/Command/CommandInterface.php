<?php


namespace Lazzard\FtpClient\Command;


use Lazzard\FtpClient\Exception\CommandException;

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
    public function getResponseEndMessage();

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

    /**
     * Send a request to FTP server for execution an arbitrary command.
     *
     * @param string $command
     *
     * @return FtpCommand Return CommandException Instance
     */
    public function rawRequest($command);

    /**
     * Send a request to FTP server for execution a SITE command.
     *
     * @param string $command
     *
     * @return FtpCommand Return CommandException Instance
     *
     * @throws CommandException
     */
    public function siteRequest($command);

    /**
     * Send a request to FTP server for execution a SITE EXEC command.
     *
     * @param string $command
     *
     * @return FtpCommand Return CommandException Instance
     *
     * @throws CommandException
     */
    public function execRequest($command);

}