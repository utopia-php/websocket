<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use Utopia\WebSocket;

$adapter = new Utopia\WebSocket\Adapter\Swoole();

$server = new WebSocket\Server($adapter);
$server->onStart(function () {
    echo "Server Started.";
});

$server->onMessage(function (Server $_server, Frame $frame) use ($server) {
    echo "$frame->data}\n";

    $server->send([$frame->fd], $frame->data);
});

$server->start();