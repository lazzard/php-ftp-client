<?php declare(strict_types=1);

namespace Lazzard\FtpClient\Connection;

class FooFtpConnection extends Connection
{
    /**
     * @inheritDoc
     */
    protected function connect() : void
    {
        //
    }

    /**
     * @param resource $stream
     */
    public function setStream($stream): void
    {
        $this->stream = $stream;
    }
}
