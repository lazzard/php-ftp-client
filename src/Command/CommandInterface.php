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
     * Send a command to the remote server.
     *
     * @param $command
     *
     * @return bool Return true in success false if fail
     */
    public function request($command);

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
     * Get server response body for the previous command request.
     *
     * @return array|null
     */
    public function getResponseBody();
}