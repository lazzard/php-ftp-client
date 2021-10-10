<?php declare(strict_types=1);

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
 * Wrapping the FTP extension functions to execute custom client commands in the server.
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
    public function getConnection() : ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * @param ConnectionInterface $connection
     *
     * @since 1.5.3
     *
     * @return void
     */
    public function setConnection(ConnectionInterface $connection) : void
    {
        $this->connection = $connection;
    }

    /**
     * @param FtpWrapper $wrapper
     *
     * @return void
     */
    public function setWrapper(FtpWrapper $wrapper) : void
    {
        $this->wrapper = $wrapper;
    }

    /**
     * @since 1.5.3
     *
     * @return FtpWrapper
     */
    public function getWrapper() : FtpWrapper
    {
        return $this->wrapper;
    }

    /**
     * Executes an arbitrary command on the server.
     *
     * @param string $command The command to execute.
     *
     * @return array Returns an array of the response information containing
     *               the [response, code, message, body, end-message, success].
     * 
     * @throws CommandException
     */
    public function raw(string $command) : array
    {
        $trimmed = trim($command);

        if (!$raw = $this->wrapper->raw($trimmed)) {
            throw new CommandException("Failed to execute the [$trimmed] command on the server.");
        }

        return $this->parseRawResponse($raw);
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
    public function site(string $command) : bool
    {
        $trimmed = trim($command);

        if (!$this->wrapper->site(trim($command))) {
            throw new CommandException($this->wrapper->getErrorMessage() 
                ?: "Failed to execute the SITE command [$trimmed] on the server.");
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
    public function exec(string $command) : bool
    {
        if (!in_array('EXEC', $this->supportedSiteCommands())) {
            throw new CommandException("SITE EXEC command feature not provided by the FTP server.");
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
     *
     * @throws CommandException
     */
    public function supportedSiteCommands() : array
    {
        if (!$response = $this->raw("SITE HELP")) {
            return $response['message'];
        }

        return array_map('ltrim', $response['body']);
    }

    /**
     * @param array $response
     *
     * @return array
     */
    protected function parseRawResponse(array $response) : array
    {
        $code = $message = $body = $endMessage = null;

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
