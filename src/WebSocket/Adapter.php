<?php
namespace Utopia\WebSocket;

/**
 * Utopia PHP Framework
 *
 * @package Utopia\WebSocket
 *
 * @link https://github.com/utopia-php/framework
 * @version 1.0 RC1
 * @license The MIT License (MIT) <http://www.opensource.org/licenses/mit-license.php>
 */
abstract class Adapter
{
    protected string $host;
    protected int $port;
    protected array $config = [];

    function __construct(string $host = '0.0.0.0', int $port = 80) {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Starts the Server.
     * @return void 
     */
    public abstract function start(): void;

    /**
     * Shuts down the Server.
     * @return void 
     */
    public abstract function shutdown(): void;

    /**
     * Sends a message to passed connections.
     * @param array $connections Array of connection ID's.
     * @param string $message Message.
     * @return void 
     */
    public abstract function send(array $connections, string $message): void;

    /**
     * Closes a connection.
     * @param int $connection Connection ID.
     * @param int $code Close Code.
     * @return void 
     */
    public abstract function close(int $connection, int $code): void;

    /**
     * Is called when the Server starts.
     * @param callable $callback 
     * @return self 
     */
    public abstract function onStart(callable $callback): self;

    /**
     * Is called when a Worker starts.
     * @param callable $callback 
     * @return self 
     */
    public abstract function onWorkerStart(callable $callback): self;

    /**
     * Is called when a connection is established.
     * @param callable $callback 
     * @return self 
     */
    public abstract function onOpen(callable $callback): self;

    /**
     * Is called when a message is received.
     * @param callable $callback 
     * @return self 
     */
    public abstract function onMessage(callable $callback): self;

    /**
     * Is called when a connection is closed.
     * @param callable $callback 
     * @return self 
     */
    public abstract function onClose(callable $callback): self;

    /**
     * Sets maximum package length in bytes.
     * @param int $bytes 
     * @return Adapter 
     */
    public abstract function setPackageMaxLength(int $bytes): self;

    /**
     * Enables/Disables compression.
     * @param bool $enabled 
     * @return Adapter 
     */
    public abstract function setCompressionEnabled(bool $enabled): self;

    /**
     * Sets the number of workers.
     * @param int $num 
     * @return Adapter 
     */
    public abstract function setWorkerNumber(int $num): self;

    /**
     * Returns the native server object from the Adapter.
     * @return mixed 
     */
    public abstract function getNative(): mixed;

    /**
     * Returns all connections.
     * @return array 
     */
    public abstract function getConnections(): array;
}