# Utopia WebSocket

[![Build Status](https://travis-ci.com/utopia-php/system.svg?branch=main)](https://travis-ci.com/utopia-php/websocket)
![Total Downloads](https://img.shields.io/packagist/dt/utopia-php/websocket.svg)
[![Discord](https://img.shields.io/discord/564160730845151244?label=discord)](https://appwrite.io/discord)

Utopia WebSocket is a simple and lite abstraction layer around a WebSocket server. This library is aiming to be as simple and easy to learn and use. This library is maintained by the [Appwrite team](https://appwrite.io).

Although this library is part of the [Utopia Framework](https://github.com/utopia-php/framework) project it is dependency free and can be used as standalone with any other PHP project or framework.

## Getting Started

Install using composer:
```bash
composer require utopia-php/websocket
```

Init in your application:
```php
<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Utopia\WebSocket;

$adapter = new WebSocket\Adapter\Swoole();
$adapter->setPackageMaxLength(64000);

$server = new WebSocket\Server($adapter);
$server->onStart(function () {
    echo "Server started!";
});
$server->onWorkerStart(function (int $workerId) {
    echo "Worker {$workerId} started!";
});
$server->onOpen(function (int $connection, $request) {
    echo "Connection {$connection} established!";
});
$server->onMessage(function (int $connection, string $message) {
    echo "Message from {$connection}: {$message}";
});
$server->onClose(function (int $connection) {
    echo "Connection {$workerId} closed!";
});

$server->start();
```

## System Requirements

Utopia Framework requires PHP 8.0 or later. We recommend using the latest PHP version whenever possible.

## Copyright and license

The MIT License (MIT) [http://www.opensource.org/licenses/mit-license.php](http://www.opensource.org/licenses/mit-license.php)
