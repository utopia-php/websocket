<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Swoole\Http\Request;
use Swoole\Process;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as SwooleServer;
use Utopia\WebSocket;
use Workerman\Connection\ConnectionInterface;
use Workerman\Connection\TcpConnection;

$adapter = new WebSocket\Adapter\Workerman();
$server = new WebSocket\Server($adapter);

$server->onOpen(function (TcpConnection $connection) {
    echo "connected ", $connection->id;
});

$server->onClose(function (TcpConnection $connection) {
    echo "disconnected ", $connection->id;
});

$server->onMessage(function (TcpConnection $connection, string $data) use ($server) {
    echo $data, PHP_EOL;

    switch ($data) {
        case 'ping':
            $server->send([$connection], 'pong');
            break;
        case 'pong':
            $server->send([$connection], 'ping');
            break;
        case 'broadcast':
            $server->send(TcpConnection::$connections, 'broadcast');
            break;
        case 'disconnect':
            $server->send([$connection], 'disconnect');
            $server->close($connection, 1000);
            break;
    }
});

$adapter->setWorkerNumber(1); // Important for tests
$server->start();
