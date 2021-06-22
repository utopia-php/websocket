<?php

namespace Utopia\WebSocket\Adapter;

use Utopia\WebSocket\Adapter;

/**
 * 
 * @package Utopia\WebSocket\Adapter
 */
class Swoole extends Adapter
{
    protected \Swoole\WebSocket\Server $server;

    protected string $host;
    protected int $port;

    public function __construct(string $host = '0.0.0.0', int $port = 80)
    {
        parent::__construct($host, $port);

        $this->server = new \Swoole\WebSocket\Server($this->host, $this->port);
    }

    public function start(): void
    {
        $this->server->set($this->config);
        $this->server->start();
    }

    public function shutdown(): void
    {
        $this->server->shutdown();
    }

    public function send(array $connections, string $message): void
    {
        foreach ($connections as $connection) {
            if ($this->server->exist($connection) && $this->server->isEstablished($connection)) {
                $this->server->push(
                    $connection,
                    $message,
                    SWOOLE_WEBSOCKET_OPCODE_TEXT,
                    SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS
                );
            } else {
                $this->server->close($connection);
            }
        }
    }

    public function close(string $connection, int $code): void
    {
        $this->server->close($connection);
    }

    public function onStart(callable $callback): self
    {
        $this->server->on('start', $callback);
        return $this;
    }

    public function onWorkerStart(callable $callback): self
    {
        $this->server->on('workerStart', $callback);
        return $this;
    }

    public function onOpen(callable $callback): self
    {
        $this->server->on('open', $callback);
        return $this;
    }

    public function onMessage(callable $callback): self
    {
        $this->server->on('message', $callback);
        return $this;
    }

    public function onClose(callable $callback): self
    {
        $this->server->on('close', $callback);
        return $this;
    }

    public function setPackageMaxLength(int $bytes): self
    {
        $this->config['package_max_length'] = $bytes;
        return $this;
    }

    public function setCompressionEnabled(bool $enabled): self
    {
        $this->config['websocket_compression'] = $enabled;
        return $this;
    }
}
