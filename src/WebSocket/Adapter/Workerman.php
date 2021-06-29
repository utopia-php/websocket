<?php

namespace Utopia\WebSocket\Adapter;

use Utopia\WebSocket\Adapter;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;

/**
 * 
 * @package Utopia\WebSocket\Adapter
 */
class Workerman extends Adapter
{
    protected Worker $server;

    protected string $host;
    protected int $port;

    private mixed $callbackOnStart;

    public function __construct(string $host = '0.0.0.0', int $port = 80)
    {
        parent::__construct($host, $port);

        $this->server = new Worker("websocket://{$this->host}:{$this->port}");
    }

    public function start(): void
    {
        Worker::runAll();
        call_user_func($this->callbackOnStart);
    }

    public function shutdown(): void
    {
        Worker::stopAll();
    }

    public function send(array $connections, string $message): void
    {
        foreach ($connections as $connection) {
            TcpConnection::$connections[$connection]->send($message);
        }
    }

    public function close($connection, int $code): void
    {
        TcpConnection::$connections[$connection]->close();
    }

    public function onStart(callable $callback): self
    {
        $this->callbackOnStart = $callback;
        return $this;
    }

    public function onWorkerStart(callable $callback): self
    {
        $this->server->onWorkerStart = function(Worker $worker) use ($callback): void {
            call_user_func($callback, $worker->id);
        };
        return $this;
    }

    public function onOpen(callable $callback): self
    {
        $this->server->onConnect = function (mixed $connection) use ($callback): void {
            $connection->onWebSocketConnect = function(TcpConnection $connection) use ($callback): void
            {
                $headers = [];
                foreach ($_SERVER as $key => $value) {
                    if(0 === strpos($key, 'HTTP_')) {
                        $headers[substr($key, strlen('HTTP_')).''] = $value;
                    }
                }
                call_user_func($callback, $connection->id, $headers);
            };
        };
        return $this;
    }

    public function onMessage(callable $callback): self
    {
        $this->server->onMessage = function (TcpConnection $connection, string $data) use ($callback): void {
            call_user_func($callback, $connection->id, $data);
        };
        return $this;
    }

    public function onClose(callable $callback): self
    {
        $this->server->onClose = function (TcpConnection $connection) use ($callback): void {
            call_user_func($callback, $connection->id);
        };
        return $this;
    }

    public function setPackageMaxLength(int $bytes): self
    {
        return $this;
    }

    public function setCompressionEnabled(bool $enabled): self
    {
        return $this;
    }

    public function setWorkerNumber(int $num): self
    {
        $this->server->count = $num;
        return $this;
    }

    public function getNative(): Worker
    {
        return $this->server;
    }

    public function getConnections(): array
    {
        return array_keys(TcpConnection::$connections);
    }
}
