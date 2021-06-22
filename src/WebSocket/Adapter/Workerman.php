<?php

namespace Utopia\WebSocket\Adapter;

use Utopia\WebSocket\Adapter;
use Workerman\Connection\TcpConnection;

/**
 * 
 * @package Utopia\WebSocket\Adapter
 */
class Workerman extends Adapter
{
    protected \Workerman\Worker $server;

    protected string $host;
    protected int $port;

    public function __construct(string $host = '0.0.0.0', int $port = 80)
    {
        parent::__construct($host, $port);

        $this->server = new \Workerman\Worker("websocket://{$this->host}:{$this->port}");
    }

    public function start(): void
    {
        \Workerman\Worker::runAll();
    }

    public function shutdown(): void
    {
        \Workerman\Worker::stopAll();
    }

    public function send(array $connections, string $message): void
    {
        foreach ($connections as $connection) {
            $connection->send($message);
        }
    }

    /**
     * 
     * @param TcpConnection $connection 
     * @param int $code 
     * @return void 
     */
    public function close($connection, int $code): void
    {
        $connection->close();
    }

    public function onStart(callable $callback): self
    {
        return $this;
    }

    public function onWorkerStart(callable $callback): self
    {
        $this->server->onWorkerStart = $callback;
        return $this;
    }

    public function onOpen(callable $callback): self
    {
        $this->server->onConnect = $callback;
        return $this;
    }

    public function onMessage(callable $callback): self
    {
        $this->server->onMessage = $callback;
        return $this;
    }

    public function onClose(callable $callback): self
    {
        $this->server->onClose = $callback;
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
}
