<?php


namespace Lazzard\FtpClient\Command;


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
}