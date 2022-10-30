<?php

require_once __DIR__.'/../../../vendor/autoload.php';

use Swoole\Http\Request;
use Utopia\WebSocket;

$adapter = new WebSocket\Adapter\Swoole();
$adapter->setWorkerNumber(1); // Important for tests

$server = new WebSocket\Server($adapter);

$server
    ->onWorkerStart(function (int $workerId) {
        echo 'worker started ', $workerId, PHP_EOL;
    })
    ->onOpen(function (int $connection, Request $request) {
        echo 'connected ', $connection, PHP_EOL;
    })
    ->onClose(function (int $connection) {
        echo 'disconnected ', $connection, PHP_EOL;
    })
    ->onMessage(function (int $connection, string $message) use ($server) {
        echo $message, PHP_EOL;

        switch ($message) {
            case 'ping':
                $server->send([$connection], 'pong');
                break;
            case 'pong':
                $server->send([$connection], 'ping');
                break;
            case 'broadcast':
                $server->send($server->getConnections(), 'broadcast');
                break;
            case 'disconnect':
                $server->send([$connection], 'disconnect');
                $server->close($connection, 1000);
                break;
        }
    })
    ->start();
