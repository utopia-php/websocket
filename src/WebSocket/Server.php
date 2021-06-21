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
    protected string $host;
    protected int $port;

    /**
     * Creates an instance of a WebSocker server.
     * @param Adapter $adapter 
     * @param string $host 
     * @param int $port 
     */
    public function __construct(Adapter $adapter, string $host = '0.0.0.0', int $port = 80)
    {
        $this->adapter = $adapter;
        $this->host = $host;
        $this->port = $port;
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
     * @return Server 
     */
    public function onStart(callable $callback): Server
    {
        $this->adapter->onStart($callback);
        return $this;
    }

    /**
     * Is called when a Worker starts.
     * @param callable $callback 
     * @return Server 
     */
    public function onWorkerStart(callable $callback): Server
    {
        $this->adapter->onWorkerStart($callback);
        return $this;
    }

    /**
     * Is called when a connection is established.
     * @param callable $callback 
     * @return Server 
     */
    public function onOpen(callable $callback): Server
    {
        $this->adapter->onOpen($callback);
        return $this;
    }

    /**
     * Is called when a message is received.
     * @param callable $callback 
     * @return Server 
     */
    public function onMessage(callable $callback): Server
    {
        $this->adapter->onMessage($callback);
        return $this;
    }

    /**
     * Is called when a connection is closed.
     * @param callable $callback 
     * @return Server 
     */
    public function onClose(callable $callback): Server
    {
        $this->adapter->onClose($callback);
        return $this;
    }
}
