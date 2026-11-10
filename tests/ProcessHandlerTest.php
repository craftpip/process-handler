<?php

namespace Craftpip\Tests;

use Craftpip\ProcessHandler\Exception\ProcessHandlerException;
use Craftpip\ProcessHandler\ProcessHandler;
use PHPUnit\Framework\TestCase;

class ProcessHandlerTest extends TestCase
{
    public function testConstructorSetsOperatingSystem(): void
    {
        $handler = new ProcessHandler();
        $this->assertNotEmpty($handler->operatingSystem);
        $this->assertContains($handler->operatingSystem, ['Unix', 'MacOS', 'Win']);
    }

    public function testConstructorReturnsApiInstance(): void
    {
        $handler = new ProcessHandler();
        $this->assertNotNull($handler->api);
    }

    public function testGetProcessThrowsExceptionWithoutPid(): void
    {
        $this->expectException(ProcessHandlerException::class);
        $this->expectExceptionMessage('Pid is required');

        $handler = new ProcessHandler();
        $handler->getProcess();
    }

    public function testIsRunningReturnsBoolForCurrentOs(): void
    {
        $handler = new ProcessHandler();
        $result = $handler->isRunning(1);
        $this->assertIsBool($result);
    }

    public function testGetAllProcessesReturnsArray(): void
    {
        $handler = new ProcessHandler();
        $processes = $handler->getAllProcesses();
        $this->assertIsArray($processes);
    }

    public function testSetPidReturnsSelf(): void
    {
        $handler = new ProcessHandler();
        $result = $handler->setPid(123);
        $this->assertSame($handler, $result);
    }
}
