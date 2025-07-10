<?php

require_once __DIR__.'/../../../vendor/autoload.php';

use Utopia\WebSocket;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;

$adapter = new WebSocket\Adapter\Workerman();
$adapter->setWorkerNumber(1); // Important for tests

$server = new WebSocket\Server($adapter);

$server
    ->onWorkerStart(function (int $workerId) {
        echo 'worker started ', $workerId, PHP_EOL;
    })
    ->onWorkerStop(function (int $workerId) {
        echo "worker stopped ", $workerId, PHP_EOL;
    })
    ->onOpen(function (int $connection, array $request) {
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
    ->onRequest(function (TcpConnection $connection, Request $request) use ($server) {
        $path = $request->path();
        if (!is_string($path)) {
            throw new \Exception('Invalid path ' . $path . ' for request: ' . json_encode($request, JSON_PRETTY_PRINT));
        }
        echo 'HTTP request received: ', $path, PHP_EOL;

        if ($path === '/health') {
            $connection->send('HTTP/1.1 200 OK' . "\r\n" .
                             'Content-Type: application/json' . "\r\n" .
                             'Connection: close' . "\r\n\r\n" .
                             json_encode(['status' => 'ok', 'message' => 'WebSocket server is running']));
        } elseif ($request->path() === '/info') {
            $connection->send('HTTP/1.1 200 OK' . "\r\n" .
                             'Content-Type: application/json' . "\r\n" .
                             'Connection: close' . "\r\n\r\n" .
                             json_encode([
                                 'server' => 'Workerman WebSocket',
                                 'connections' => count($server->getConnections()),
                                 'timestamp' => time()
                             ]));
        } else {
            $connection->send('HTTP/1.1 404 Not Found' . "\r\n" .
                             'Connection: close' . "\r\n\r\n" .
                             'Not Found');
        }
    })
    ->start();
