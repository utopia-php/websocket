<?php

namespace Utopia\WebSocket;

abstract class Adapter
{
    protected string $host;

    protected int $port;

    /**
     * @var array<int|string,bool|int|string>
     */
    protected array $config = [];

    public function __construct(string $host = '0.0.0.0', int $port = 80)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Starts the Server.
     *
     * @return void
     */
    abstract public function start(): void;

    /**
     * Shuts down the Server.
     *
     * @return void
     */
    abstract public function shutdown(): void;

    /**
     * Sends a message to passed connections.
     *
     * @param  array<mixed,mixed>  $connections Array of connection ID's.
     * @param  string  $message Message.
     * @return void
     */
    abstract public function send(array $connections, string $message): void;

    /**
     * Closes a connection.
     *
     * @param  int  $connection Connection ID.
     * @param  int  $code Close Code.
     * @return void
     */
    abstract public function close(int $connection, int $code): void;

    /**
     * Is called when the Server starts.
     *
     * @param  callable  $callback
     * @return self
     */
    abstract public function onStart(callable $callback): self;

    /**
     * Is called when a Worker starts.
     *
     * @param  callable  $callback
     * @return self
     */
    abstract public function onWorkerStart(callable $callback): self;

    /**
     * Is called when a connection is established.
     *
     * @param  callable  $callback
     * @return self
     */
    abstract public function onOpen(callable $callback): self;

    /**
     * Is called when a message is received.
     *
     * @param  callable  $callback
     * @return self
     */
    abstract public function onMessage(callable $callback): self;

    /**
     * Is called when a connection is closed.
     *
     * @param  callable  $callback
     * @return self
     */
    abstract public function onClose(callable $callback): self;

    /**
     * Sets maximum package length in bytes.
     *
     * @param  int  $bytes
     * @return Adapter
     */
    abstract public function setPackageMaxLength(int $bytes): self;

    /**
     * Enables/Disables compression.
     *
     * @param  bool  $enabled
     * @return Adapter
     */
    abstract public function setCompressionEnabled(bool $enabled): self;

    /**
     * Sets the number of workers.
     *
     * @param  int  $num
     * @return Adapter
     */
    abstract public function setWorkerNumber(int $num): self;

    /**
     * Returns the native server object from the Adapter.
     *
     * @return mixed
     */
    abstract public function getNative(): mixed;

    /**
     * Returns all connections.
     *
     * @return array<mixed>
     */
    abstract public function getConnections(): array;
}
