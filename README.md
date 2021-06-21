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
$adapter->setConfig('max_connections', 128);

$server = new WebSocket\Server($adapter);
$server->onStart(/* callback */);
$server->onWorkerStart(/* callback */);
$server->onMessage(/* callback */);
$server->onOpen(/* callback */);
$server->onClose(/* callback */);

$server->start();
```

## System Requirements

Utopia Framework requires PHP 8.0 or later. We recommend using the latest PHP version whenever possible.

## Authors

**Eldad Fux**

+ [https://twitter.com/eldadfux](https://twitter.com/eldadfux)
+ [https://github.com/eldadfux](https://github.com/eldadfux)

**Torsten Dittmann**

+ [https://twitter.com/dittmanntorsten](https://twitter.com/dittmanntorsten)
+ [https://github.com/torstendittmann](https://github.com/torstendittmann)

## Copyright and license

The MIT License (MIT) [http://www.opensource.org/licenses/mit-license.php](http://www.opensource.org/licenses/mit-license.php)
