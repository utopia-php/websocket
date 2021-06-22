<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Swoole\Process;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use Utopia\WebSocket;

$adapter = new Utopia\WebSocket\Adapter\Swoole();

$server = new WebSocket\Server($adapter);
$server->onStart(function ($server) {
    echo "Server Started.";

    Process::signal(2, function () use ($server) {
        $server->shutdown();
    });
});

$server->onMessage(function (Server $_server, Frame $frame) use ($server) {
    echo $frame->data, PHP_EOL;

    switch ($frame->data) {
        case 'ping':
            $server->send([$frame->fd], 'pong');
            break;
        case 'disconnect':
            $server->send([$frame->fd], 'disconnect');
            $server->close($frame->fd, 1000);
            break;
    }
});

$server->start();