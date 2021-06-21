<?php
namespace Utopia\WebSocket;

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
abstract class Adapter
{
    protected array $config;

    function __construct(array $config = []) {
        $this->config = $config;
    }

    /**
     * Returns configuration. Pass no argument to return the complete config.
     * @param string $key 
     * @return string|array 
     */
    public function getConfig(string $key = ''): mixed
    {
        return empty($key) ? $this->config : $this->config[$key];
    }

    /**
     * Set config key.
     * @param string $key 
     * @param mixed $value 
     * @return Adapter 
     */
    public function setConfig(string $key, mixed $value): Adapter
    {
        $this->config[$key] = $value;

        return $this;
    }

    /**
     * Starts the Server.
     * @return void 
     */
    public abstract function start(): void;

    /**
     * Sends a message to passed connections.
     * @param array $connections Array of connection ID's.
     * @param string $message Message.
     * @return void 
     */
    public abstract function send(array $connections, string $message): void;

    /**
     * Closes a connection.
     * @param string $connection Connection ID.
     * @param int $code Close Code.
     * @return void 
     */
    public abstract function close(string $connection, int $code): void;

    /**
     * Is called when the Server starts.
     * @param callable $callback 
     * @return Adapter 
     */
    public abstract function onStart(callable $callback): Adapter;

    /**
     * Is called when a Worker starts.
     * @param callable $callback 
     * @return Adapter 
     */
    public abstract function onWorkerStart(callable $callback): Adapter;

    /**
     * Is called when a connection is established.
     * @param callable $callback 
     * @return Adapter 
     */
    public abstract function onOpen(callable $callback): Adapter;

    /**
     * Is called when a message is received.
     * @param callable $callback 
     * @return Adapter 
     */
    public abstract function onMessage(callable $callback): Adapter;

    /**
     * Is called when a connection is closed.
     * @param callable $callback 
     * @return Adapter 
     */
    public abstract function onClose(callable $callback): Adapter;

}