<?php

namespace Utopia\WebSocket\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\WebSocket\Client;

class ClientTest extends TestCase
{
    private Client $client;
    private string $testUrl = 'ws://localhost:8080';

    protected function setUp(): void
    {
        $this->client = new Client($this->testUrl);
    }

    public function testConstructorWithValidUrl(): void
    {
        $client = new Client($this->testUrl);
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testConstructorWithInvalidUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Client('invalid-url');
    }

    public function testConstructorWithCustomOptions(): void
    {
        $options = [
            'headers' => ['Authorization' => 'Bearer token'],
            'timeout' => 60
        ];
        $client = new Client($this->testUrl, $options);
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testEventHandlers(): void
    {
        $messageReceived = false;
        $this->client->onMessage(function ($data) use (&$messageReceived) {
            $messageReceived = true;
        });

        $closeReceived = false;
        $this->client->onClose(function () use (&$closeReceived) {
            $closeReceived = true;
        });

        $errorReceived = false;
        $this->client->onError(function ($error) use (&$errorReceived) {
            $errorReceived = true;
        });

        $openReceived = false;
        $this->client->onOpen(function () use (&$openReceived) {
            $openReceived = true;
        });

        $pingReceived = false;
        $this->client->onPing(function ($data) use (&$pingReceived) {
            $pingReceived = true;
        });

        $pongReceived = false;
        $this->client->onPong(function ($data) use (&$pongReceived) {
            $pongReceived = true;
        });

        // Verify that all handlers are properly set
        $this->assertIsCallable($this->client->onMessage(...));
        $this->assertIsCallable($this->client->onClose(...));
        $this->assertIsCallable($this->client->onError(...));
        $this->assertIsCallable($this->client->onOpen(...));
        $this->assertIsCallable($this->client->onPing(...));
        $this->assertIsCallable($this->client->onPong(...));
    }

    public function testIsConnected(): void
    {
        $this->assertFalse($this->client->isConnected());
    }

    public function testSendWithoutConnection(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Not connected to WebSocket server');
        $this->client->send('test message');
    }

    public function testReceiveWithoutConnection(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Not connected to WebSocket server');
        $this->client->receive();
    }

    public function testListen(): void
    {
        $messageReceived = false;
        $testMessage = 'Hello WebSocket!';

        $this->client->onMessage(function ($data) use (&$messageReceived, $testMessage) {
            $messageReceived = true;
            $this->assertEquals($testMessage, $data);
        });

        // Mock the client's recv method to simulate receiving a message
        $mockFrame = new \Swoole\WebSocket\Frame();
        $mockFrame->opcode = WEBSOCKET_OPCODE_TEXT;
        $mockFrame->data = $testMessage;

        $swooleClient = $this->getMockBuilder(\Swoole\Coroutine\Http\Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $swooleClient->expects($this->exactly(2))
            ->method('recv')
            ->willReturnOnConsecutiveCalls($mockFrame, false);

        $swooleClient->errCode = SWOOLE_ERROR_CLIENT_NO_CONNECTION;

        // Use reflection to set the private properties
        $reflectionClass = new \ReflectionClass(Client::class);

        $connectedProperty = $reflectionClass->getProperty('connected');
        $connectedProperty->setAccessible(true);
        $connectedProperty->setValue($this->client, true);

        $clientProperty = $reflectionClass->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($this->client, $swooleClient);

        $this->client->listen();

        $this->assertTrue($messageReceived);
        $this->assertFalse($this->client->isConnected());
    }

    public function testListenWithError(): void
    {
        $errorReceived = false;
        $this->client->onError(function ($error) use (&$errorReceived) {
            $errorReceived = true;
            $this->assertInstanceOf(\RuntimeException::class, $error);
        });

        $swooleClient = $this->getMockBuilder(\Swoole\Coroutine\Http\Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $swooleClient->expects($this->once())
            ->method('recv')
            ->willReturn(false);

        $swooleClient->errCode = 1; // Some error code that's not SWOOLE_ERROR_CLIENT_NO_CONNECTION
        $swooleClient->errMsg = 'Test error';

        // Use reflection to set the private properties
        $reflectionClass = new \ReflectionClass(Client::class);

        $connectedProperty = $reflectionClass->getProperty('connected');
        $connectedProperty->setAccessible(true);
        $connectedProperty->setValue($this->client, true);

        $clientProperty = $reflectionClass->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($this->client, $swooleClient);

        $this->client->listen();

        $this->assertTrue($errorReceived);
        $this->assertFalse($this->client->isConnected());
    }
}
