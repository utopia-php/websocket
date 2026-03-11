<?php

namespace Utopia\WebSocket\Tests;

use PHPUnit\Framework\TestCase;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use Utopia\WebSocket\Adapter\Swoole;

class SwooleAdapterTest extends TestCase
{
    private function createAdapterWithMockServer(): array
    {
        try {
            $adapter = $this->getMockBuilder(Swoole::class)
                ->disableOriginalConstructor()
                ->onlyMethods([])
                ->getMock();

            $mockServer = $this->getMockBuilder(Server::class)
                ->disableOriginalConstructor()
                ->getMock();
        } catch (\Error $e) {
            if (strpos($e->getMessage(), 'enum_exists') !== false) {
                $this->markTestSkipped('Test skipped due to enum_exists compatibility issue');
            }
            throw $e;
        }

        $reflection = new \ReflectionClass(Swoole::class);
        $serverProperty = $reflection->getProperty('server');
        $serverProperty->setAccessible(true);
        $serverProperty->setValue($adapter, $mockServer);

        return [$adapter, $mockServer];
    }

    public function testOnMessageSkipsEmptyData(): void
    {
        [$adapter, $mockServer] = $this->createAdapterWithMockServer();

        $registeredCallback = null;
        $mockServer->expects($this->once())
            ->method('on')
            ->with('message', $this->callback(function ($callback) use (&$registeredCallback) {
                $registeredCallback = $callback;
                return true;
            }));

        $callbackInvoked = false;
        $adapter->onMessage(function (int $connection, string $message) use (&$callbackInvoked) {
            $callbackInvoked = true;
        });

        $this->assertNotNull($registeredCallback, 'Server on() should have been called');

        // Simulate a frame with empty data
        $frame = new Frame();
        $frame->fd = 1;
        $frame->data = '';

        $registeredCallback($mockServer, $frame);

        $this->assertFalse($callbackInvoked, 'Callback should not be invoked for empty message data');
    }

    public function testOnMessageCallsCallbackWithValidData(): void
    {
        [$adapter, $mockServer] = $this->createAdapterWithMockServer();

        $registeredCallback = null;
        $mockServer->expects($this->once())
            ->method('on')
            ->with('message', $this->callback(function ($callback) use (&$registeredCallback) {
                $registeredCallback = $callback;
                return true;
            }));

        $receivedFd = null;
        $receivedData = null;
        $adapter->onMessage(function (int $connection, string $message) use (&$receivedFd, &$receivedData) {
            $receivedFd = $connection;
            $receivedData = $message;
        });

        // Simulate a frame with valid data
        $frame = new Frame();
        $frame->fd = 42;
        $frame->data = '{"type":"authentication"}';

        $registeredCallback($mockServer, $frame);

        $this->assertEquals(42, $receivedFd);
        $this->assertEquals('{"type":"authentication"}', $receivedData);
    }
}
