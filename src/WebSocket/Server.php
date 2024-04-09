<?php

namespace Utopia\WebSocket;

use Throwable;

class Server
{
    /**
     * Callbacks that will be executed when an error occurs
     * @var array<callable>
     */
    protected array $errorCallbacks = [];

    protected Adapter $adapter;

    /**
     * Creates an instance of a WebSocket server.
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
        try {
            $this->adapter->start();
        } catch(Throwable $error) {
            foreach ($this->errorCallbacks as $errorCallback) {
                $errorCallback($error, 'start');
            }
        }
    }

    /**
     * Shuts down the WebSocket server.
     * @return void
     */
    public function shutdown(): void
    {
        try {
            $this->adapter->shutdown();
        } catch(Throwable $error) {
            foreach ($this->errorCallbacks as $errorCallback) {
                $errorCallback($error, 'shutdown');
            }
        }
    }

    /**
     * Sends a message to passed connections.
     * @param array<mixed, mixed> $connections Array of connection ID's.
     * @param string $message Message.
     * @return void
     */
    public function send(array $connections, string $message): void
    {
        try {
            $this->adapter->send($connections, $message);
        } catch(Throwable $error) {
            foreach ($this->errorCallbacks as $errorCallback) {
                $errorCallback($error, 'send');
            }
        }
    }

    /**
     * Closes a connection.
     *
     * @param  int  $connection Connection ID.
     * @param  int  $code Close Code.
     * @return void
     */
    public function close(int $connection, int $code): void
    {
        try {
            $this->adapter->close($connection, $code);
        } catch(Throwable $error) {
            foreach ($this->errorCallbacks as $errorCallback) {
                $errorCallback($error, 'close');
            }
        }
    }

    /**
     * Is called when the Server starts.
     * @param callable $callback
     * @return self
     */
    public function onStart(callable $callback): self
    {
        try {
            $this->adapter->onStart($callback);
        } catch(Throwable $error) {
            foreach ($this->errorCallbacks as $errorCallback) {
                $errorCallback($error, 'onStart');
            }
        }

        return $this;
    }

    /**
     * Is called when a Worker starts.
     * @param callable $callback
     * @return self
     */
    public function onWorkerStart(callable $callback): self
    {
        try {
            $this->adapter->onWorkerStart($callback);
        } catch(Throwable $error) {
            foreach ($this->errorCallbacks as $errorCallback) {
                $errorCallback($error, 'onWorkerStart');
            }
        }

        return $this;
    }

    /**
     * Is called when a Worker stops.
     * @param callable $callback
     * @return self
     */
    public function onWorkerStop(callable $callback): self
    {
        try {
            $this->adapter->onWorkerStop($callback);
        } catch(Throwable $error) {
            foreach ($this->errorCallbacks as $errorCallback) {
                $errorCallback($error, 'onWorkerStop');
            }
        }

        return $this;
    }

    /**
     * Is called when a connection is established.
     * @param callable $callback
     * @return self
     */
    public function onOpen(callable $callback): self
    {
        try {
            $this->adapter->onOpen($callback);
        } catch(Throwable $error) {
            foreach ($this->errorCallbacks as $errorCallback) {
                $errorCallback($error, 'onOpen');
            }
        }

        return $this;
    }

    /**
     * Is called when a message is received.
     * @param callable $callback
     * @return self
     */
    public function onMessage(callable $callback): self
    {
        try {
            $this->adapter->onMessage($callback);
        } catch(Throwable $error) {
            foreach ($this->errorCallbacks as $errorCallback) {
                $errorCallback($error, 'onMessage');
            }
        }

        return $this;
    }

    /**
     * Is called when a connection is closed.
     * @param callable $callback
     * @return self
     */
    public function onClose(callable $callback): self
    {
        try {
            $this->adapter->onClose($callback);
        } catch(Throwable $error) {
            foreach ($this->errorCallbacks as $errorCallback) {
                $errorCallback($error, 'onClose');
            }
        }

        return $this;
    }

    /**
     * Returns all connections.
     *
     * @return array<mixed>
     */
    public function getConnections(): array
    {
        return $this->adapter->getConnections();
    }

    /**
     * Register callback. Will be executed when error occurs.
     *
     * @param  callable  $callback
     * @return self
     */
    public function error(callable $callback): self
    {
        $this->errorCallbacks[] = $callback;
        return $this;
    }
}
