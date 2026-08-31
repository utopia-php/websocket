<?php

namespace Utopia\WebSocket\Adapter;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Process;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use Utopia\WebSocket\Adapter;

class Swoole extends Adapter
{
    protected Server $server;

    protected string $host;

    protected int $port;

    /**
     * @param int $socketBufferSize Bytes the reactor may hold per connection for a
     *   client that is not keeping up; 0 leaves it uncapped, which is Swoole's default.
     *
     *   Uncapped, a client that stops draining makes the reactor buffer every
     *   undelivered frame in process memory without limit while push() still reports
     *   success: 300 non-draining connections held 2.27GB, all of it in the reactor
     *   rather than the PHP worker. Capped, memory bounds at size x connections and
     *   push() returns false for a connection that is over, which send() turns into a
     *   close. Taken here rather than through a setter because it is read once, when
     *   start() hands the config to Swoole, so changing it later would silently do
     *   nothing.
     */
    public function __construct(string $host = '0.0.0.0', int $port = 80, int $socketBufferSize = 0)
    {
        parent::__construct($host, $port);

        $this->server = new Server($this->host, $this->port);

        // Set maximum connections to Swoole's limit of 1 Million
        $this->config['max_connection'] = 1_000_000;

        if ($socketBufferSize > 0) {
            $this->config['socket_buffer_size'] = $socketBufferSize;
            // Left on, an over-budget push suspends and the worker accumulates the
            // backlog against PHP's memory_limit, fatalling the worker instead of
            // shedding the one connection that is behind.
            $this->config['send_yield'] = false;
        }
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
        $flags = SWOOLE_WEBSOCKET_FLAG_FIN;
        if ($this->config['websocket_compression'] ?? false) {
            $flags |= SWOOLE_WEBSOCKET_FLAG_COMPRESS;
        }

        foreach ($connections as $connection) {
            go(function () use ($connection, $message, $flags) {
                if (!$this->server->exist($connection) || !$this->server->isEstablished($connection)) {
                    $this->server->close($connection);

                    return;
                }

                $pushed = $this->server->push(
                    $connection,
                    $message,
                    SWOOLE_WEBSOCKET_OPCODE_TEXT,
                    $flags
                );

                // Only reachable when the buffer is capped (see $socketBufferSize).
                // The client is far enough behind that its output buffer is full;
                // dropping the frame would leave it silently out of sync, so close
                // and let it reconnect from a known state.
                if (!$pushed) {
                    $this->server->close($connection);
                }
            });
        }
    }

    public function close(int $connection, int $code): void
    {
        $this->server->close($connection);
    }

    public function onStart(callable $callback): self
    {
        $this->server->on('start', function () use ($callback) {
            call_user_func($callback);

            Process::signal('2', function () {
                $this->shutdown();
            });
        });

        return $this;
    }

    public function onWorkerStart(callable $callback): self
    {
        $this->server->on('workerStart', function (Server $server, int $workerId) use ($callback) {
            call_user_func($callback, $workerId);
        });
        return $this;
    }

    public function onWorkerStop(callable $callback): Adapter
    {
        $this->server->on('workerStop', function (Server $server, int $workerId) use ($callback) {
            call_user_func($callback, $workerId);
        });

        return $this;
    }

    public function onOpen(callable $callback): self
    {
        $this->server->on('open', function (Server $server, Request $request) use ($callback) {
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
        $this->server->on('close', function (Server $server, int $fd) use ($callback) {
            call_user_func($callback, $fd);
        });

        return $this;
    }

    public function onRequest(callable $callback): self
    {
        $this->server->on('request', function (Request $request, Response $response) use ($callback) {
            call_user_func($callback, $request, $response);
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

    public function getNative(): Server
    {
        return $this->server;
    }
}
