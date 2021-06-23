<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Utopia\WebSocket;
use Workerman\Connection\TcpConnection;

$adapter = new WebSocket\Adapter\Workerman();
$server = new WebSocket\Server($adapter);

$server->onOpen(function (TcpConnection $connection) {
    echo "connected ", $connection->id, PHP_EOL;
});

$server->onClose(function (TcpConnection $connection) {
    echo "disconnected ", $connection->id, PHP_EOL;
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
