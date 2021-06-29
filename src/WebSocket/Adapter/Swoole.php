<?php

namespace Utopia\WebSocket\Adapter;

use Swoole\Http\Request;
use Swoole\Process;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use Utopia\WebSocket\Adapter;

/**
 * 
 * @package Utopia\WebSocket\Adapter
 */
class Swoole extends Adapter
{
    protected Server $server;

    protected string $host;
    protected int $port;

    private static array $connections = [];

    public function __construct(string $host = '0.0.0.0', int $port = 80)
    {
        parent::__construct($host, $port);

        $this->server = new Server($this->host, $this->port);
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
        $this->server->on('start', function () use ($callback) {
            call_user_func($callback);

            Process::signal(2, function () {
                $this->shutdown();
            });
        });
        return $this;
    }

    public function onWorkerStart(callable $callback): self
    {
        $this->server->on('workerStart', function(Server $server, int $workerId) use ($callback) {
            call_user_func($callback, $workerId);
        });
        return $this;
    }

    public function onOpen(callable $callback): self
    {
        $this->server->on('open', function (Server $server, Request $request) use ($callback) {
            self::$connections[$request->fd] = true;

            call_user_func($callback, $request->fd, $request);
        });
        return $this;
    }

    public function onMessage(callable $callback): self
    {
        $this->server->on('message', function (Server $server, Frame $frame) use ($callback) {
            call_user_func($callback, $frame->fd, $frame->data);
        });
        return $this;
    }

    public function onClose(callable $callback): self
    {
        $this->server->on('close', function (int $fd) use ($callback) {
            unset(self::$connections[$fd]);

            call_user_func($callback, $fd);
        });
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

    public function setWorkerNumber(int $num): self
    {
        $this->config['worker_num'] = $num;
        return $this;
    }

    public function getNative(): \Swoole\WebSocket\Server
    {
        return $this->server;
    }

    public function getConnections(): array
    {
        return array_keys(self::$connections);
    }
}
