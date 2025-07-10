<?php

require_once __DIR__.'/../../../vendor/autoload.php';

use Swoole\Http\Request;
use Swoole\Http\Response;
use Utopia\WebSocket;

$adapter = new WebSocket\Adapter\Swoole();
$adapter->setWorkerNumber(1); // Important for tests

$server = new WebSocket\Server($adapter);

$server
    ->onWorkerStart(function (int $workerId) {
        echo 'worker started ', $workerId, PHP_EOL;
    })
    ->onWorkerStop(function (int $workerId) {
        echo "worker stopped ", $workerId, PHP_EOL;
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
    ->onRequest(function (Request $request, Response $response) use ($server) {
        echo 'HTTP request received: ', $request->server['request_uri'], PHP_EOL;

        if ($request->server['request_uri'] === '/health') {
            $response->header('Content-Type', 'application/json');
            $response->status(200);
            $response->end(json_encode(['status' => 'ok', 'message' => 'WebSocket server is running']));
        } elseif ($request->server['request_uri'] === '/info') {
            $response->header('Content-Type', 'application/json');
            $response->status(200);
            $response->end(json_encode([
                'server' => 'Swoole WebSocket',
                'connections' => count($server->getConnections()),
                'timestamp' => time()
            ]));
        } else {
            $response->status(404);
            $response->end('Not Found');
        }
    })
    ->start();
