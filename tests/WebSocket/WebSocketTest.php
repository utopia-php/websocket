<?php

/**
 * Utopia PHP Framework
 *
 * @package WebSocket
 * @subpackage Tests
 *
 * @link https://github.com/utopia-php/framework
 * @author Torsten Dittmann <torsten@appwrite.io>
 * @version 1.0 RC1
 * @license The MIT License (MIT) <http://www.opensource.org/licenses/mit-license.php>
 */

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\WebSocket;

class WebSocketTest extends TestCase
{
    public function setUp(): void
    {
    }

    public function testConfig()
    {
        $adapter = new WebSocket\Adapter\Swoole();

        $this->assertIsArray($adapter->getConfig());
        $this->isEmpty($adapter->getConfig());

        $adapter->setConfig('string', 'text');
        $adapter->setConfig('int', 123);
        $adapter->setConfig('bool', true);

        $this->assertIsArray($adapter->getConfig());
        
        $this->assertIsString($adapter->getConfig('string'));
        $this->assertEquals('text', $adapter->getConfig('string'));
                
        $this->assertIsInt($adapter->getConfig('int'));
        $this->assertEquals(123, $adapter->getConfig('int'));
                
        $this->assertIsBool($adapter->getConfig('bool'));
        $this->assertEquals(true, $adapter->getConfig('bool'));
    }
}