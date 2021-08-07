<?php

/**
 * This file is part of the Lazzard/php-ftp-client package.
 *
 * (c) El Amrani Chakir <elamrani.sv.laza@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lazzard\FtpClient\Command;

use Lazzard\FtpClient\Connection\ConnectionInterface;
use Lazzard\FtpClient\Exception\CommandException;
use Lazzard\FtpClient\FtpWrapper;

/**
 * Wrapping the FTP extension functions that can be used to send raw commands to the server.
 *
 * @since  1.0
 * @author El Amrani Chakir <elamrani.sv.laza@gmail.com>
 */
class FtpCommand
{
    /** @var ConnectionInterface */
    protected $connection;

    /** @var FtpWrapper */
    protected $wrapper;

    /**
     * FtpCommand constructor.
     *
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
        $this->wrapper    = new FtpWrapper($connection);
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param FtpWrapper $wrapper
     */
    public function setWrapper($wrapper)
    {
        $this->wrapper = $wrapper;
    }

    /**
     * Sends a request to FTP server to execute an arbitrary command.
     *
     * @param string $command The command to execute.
     *
     * @return array|false Returns an array of the response information containing
     *                     the [response, code, message, body, end-message, success]
     *                     if the giving command is null or empty then a false value
     *                     returned.
     */
    public function raw($command)
    {
        $trimmed = trim($command);

        if ($trimmed !== '') {
            return $this->parseResponse($this->wrapper->raw($trimmed));
        }

        return false;
    }

    /**
     * Executes a SITE command.
     *
     * @param string $command The site command to execute.
     *
     * @return bool Returns true in success, if not an exception throws.
     *
     * @throws CommandException
     */
    public function site($command)
    {
        if (!$this->wrapper->site(trim($command))) {
            throw new CommandException($this->wrapper->getErrorMessage() ?: "SITE command was failed.");
        }

        return true;
    }

    /**
     * Sends a SITE EXEC command to FTP server.
     *
     * Note! Not all FTP servers support this command.
     *
     * @param string $command
     *
     * @return bool
     *
     * @throws CommandException
     */
    public function exec($command)
    {
        if (!in_array('exec', $this->supportedSiteCommands())) {
            throw new CommandException("SITE EXEC command not provided by the FTP server.");
        }

        if (!$this->wrapper->exec(trim($command))) {
            throw new CommandException($this->wrapper->getErrorMessage() ?: "SITE EXEC command was failed");
        }

        return true;
    }

    /**
     * Gets supported SITE commands by the remote server.
     *
     * @see FtpCommand::raw()
     *
     * @return array Returns an array of SITE available commands in success, if not
     *               the FTP reply error message returns.
     */
    public function supportedSiteCommands()
    {
        if (!$response = $this->raw("SITE HELP")) {
            return $response['message'];
        }

        return array_map('ltrim', $response['body']);
    }

    /**
     * @param string $response
     *
     * @return array
     */
    protected function parseRawResponse($response)
    {
        $code       = null;
        $message    = null;
        $body       = null;
        $endMessage = null;

        // get the response code
        if (preg_match('/^\d+/', $response[0], $matches) !== false) {
            $code = (int)$matches[0];
        }

        // get the message
        if (preg_match('/[A-z ]+.*/', $response[0], $matches) !== false) {
            $message = $matches[0];
        }

        // if the response is multiline response then search for the body and the end-message
        $count = count($response);
        if ($count > 1) {
            $body       = array_slice($response, 1, -1);
            $endMessage = $response[$count - 1];
        }

        return [
            'response'    => $response,
            'code'        => $code,
            'message'     => $message,
            'body'        => $body,
            'end-message' => $endMessage,
            'success'     => $code ? $code < 400 : null
        ];
    }
}
