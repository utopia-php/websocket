<?php
namespace Utopia\WebSocket;

use Utopia\WebSocket\Adapter;

/**
 * Utopia PHP Framework
 *
 * @package Utopia\WebSocket
 *
 * @link https://github.com/utopia-php/framework
 * @author Torsten Dittmann <torsten@appwrite.io>
 * @version 1.0 RC1
 * @license The MIT License (MIT) <http://www.opensource.org/licenses/mit-license.php>
 */
class Server
{
    protected Adapter $adapter;

    /**
     * Creates an instance of a WebSocker server.
     * @param Adapter $adapter 
     */
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Starts the WebSocket server.
     * @return void 
     */
    public function start(): void
    {
        $this->adapter->start();
    }

    /**
     * Shuts down the WebSocket server.
     * @return void 
     */
    public function shutdown(): void
    {
        $this->adapter->shutdown();
    }

    /**
     * Sends a message to passed connections.
     * @param array $connections Array of connection ID's.
     * @param string $message Message.
     * @return void 
     */
    public function send(array $connections, string $message): void
    {
        $this->adapter->send($connections, $message);
    }

    /**
     * Closes a connection.
     * @param string $connection Connection ID.
     * @param int $code Close Code.
     * @return void 
     */
    public function close(string $connection, int $code): void
    {
        $this->adapter->close($connection, $code);
    }

    /**
     * Is called when the Server starts.
     * @param callable $callback 
     * @return self 
     */
    public function onStart(callable $callback): self
    {
        $this->adapter->onStart($callback);
        return $this;
    }

    /**
     * Is called when a Worker starts.
     * @param callable $callback 
     * @return self 
     */
    public function onWorkerStart(callable $callback): self
    {
        $this->adapter->onWorkerStart($callback);
        return $this;
    }

    /**
     * Is called when a connection is established.
     * @param callable $callback 
     * @return self 
     */
    public function onOpen(callable $callback): self
    {
        $this->adapter->onOpen($callback);
        return $this;
    }

    /**
     * Is called when a message is received.
     * @param callable $callback 
     * @return self 
     */
    public function onMessage(callable $callback): self
    {
        $this->adapter->onMessage($callback);
        return $this;
    }

    /**
     * Is called when a connection is closed.
     * @param callable $callback 
     * @return self 
     */
    public function onClose(callable $callback): self
    {
        $this->adapter->onClose($callback);
        return $this;
    }
}
