<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Swoole\Http\Request;
use Swoole\Process;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as SwooleServer;
use Utopia\WebSocket;

$connections = [];

$adapter = new Utopia\WebSocket\Adapter\Swoole();
$adapter->setWorkerNumber(1);
$server = new WebSocket\Server($adapter);
$server->onStart(function ($server) {
    echo "Server Started.";

    Process::signal(2, function () use ($server) {
        $server->shutdown();
    });
});

$server->onWorkerStart(function ($server) {
    $server->connections = [];
});

$server->onOpen(function(SwooleServer $server, Request $request) {
    $server->connections[$request->fd] = true;
});

$server->onClose(function(SwooleServer $server, int $fd) {
    unset($server->connections[$fd]);
});

$server->onMessage(function(SwooleServer $swooleServer, Frame $frame) use ($server, $connections) {
    echo $frame->data, PHP_EOL;
    switch ($frame->data) {
        case 'ping':
            $server->send([$frame->fd], 'pong');
            break;
        case 'pong':
            $server->send([$frame->fd], 'ping');
            break;
        case 'broadcast':
            $server->send(array_keys($swooleServer->connections), 'broadcast');
            break;
        case 'disconnect':
            $server->send([$frame->fd], 'disconnect');
            $server->close($frame->fd, 1000);
            break;
    }
});

$server->start();