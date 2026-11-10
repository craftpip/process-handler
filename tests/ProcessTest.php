<?php

namespace Craftpip\Tests;

use Craftpip\ProcessHandler\Process;
use PHPUnit\Framework\TestCase;

class ProcessTest extends TestCase
{
    public function testGettersReturnConstructorValues(): void
    {
        $process = new Process(
            'nginx',
            '1234',
            'session0',
            '5678',
            '1024 KB',
            'RUNNING',
            'www-data',
            '00:01:23',
            'nginx: worker process'
        );

        $this->assertSame('nginx', $process->getName());
        $this->assertSame('1234', $process->getPid());
        $this->assertSame('session0', $process->getSessionName());
        $this->assertSame('5678', $process->getSession());
        $this->assertSame('1024 KB', $process->getMemUsed());
        $this->assertSame('RUNNING', $process->getStatus());
        $this->assertSame('www-data', $process->getUsername());
        $this->assertSame('00:01:23', $process->getCpuTime());
        $this->assertSame('nginx: worker process', $process->getWindowTitle());
    }

    public function testGettersWithFalseOptionalValues(): void
    {
        $process = new Process(
            'sh',
            '9999',
            false,
            '0',
            '0 KB',
            'RUNNING',
            'root',
            '00:00:00',
            false
        );

        $this->assertSame('sh', $process->getName());
        $this->assertSame('9999', $process->getPid());
        $this->assertFalse($process->getSessionName());
        $this->assertSame('0', $process->getSession());
        $this->assertFalse($process->getWindowTitle());
    }
}
