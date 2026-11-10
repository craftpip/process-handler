<?php

namespace Craftpip\Tests\Drivers;

use Craftpip\ProcessHandler\Drivers\Unix;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class UnixDriverTest extends TestCase
{
    private Unix $driver;
    private \ReflectionMethod $parseMethod;

    protected function setUp(): void
    {
        $this->driver = new Unix();
        $reflection = new ReflectionClass($this->driver);
        $this->parseMethod = $reflection->getMethod('parse');
        $this->parseMethod->setAccessible(true);
    }

    public function testParseReturnsProcessesFromPsOutput(): void
    {
        $output = "  PID TIME     SZ USER    SESS COMMAND\n"
            . "    1 00:00:03 1280 root      ? /sbin/init\n"
            . ' 1234 00:01:23 2048 www-data  1 nginx: worker process';

        /** @var \Craftpip\ProcessHandler\Process[] $processes */
        $processes = $this->parseMethod->invoke($this->driver, $output);

        $this->assertCount(2, $processes);
        $this->assertSame('1', $processes[0]->getPid());
        $this->assertSame('/sbin/init', $processes[0]->getName());
        $this->assertSame('root', $processes[0]->getUsername());
        $this->assertSame('1280 KB', $processes[0]->getMemUsed());
        $this->assertSame('RUNNING', $processes[0]->getStatus());

        $this->assertSame('1234', $processes[1]->getPid());
        $this->assertSame('nginx: worker process', $processes[1]->getName());
        $this->assertSame('www-data', $processes[1]->getUsername());
    }

    public function testParseSkipsHeaderLine(): void
    {
        $output = "  PID TIME     SZ USER    SESS COMMAND\n"
            . '    1 00:00:03 1280 root      ? /sbin/init';

        /** @var \Craftpip\ProcessHandler\Process[] $processes */
        $processes = $this->parseMethod->invoke($this->driver, $output);

        $this->assertCount(1, $processes);
    }

    public function testParseHandlesSingleProcess(): void
    {
        $output = "  PID TIME     SZ USER    SESS COMMAND\n"
            . ' 5678 00:00:01 512  mysql   123 /usr/sbin/mysqld';

        /** @var \Craftpip\ProcessHandler\Process[] $processes */
        $processes = $this->parseMethod->invoke($this->driver, $output);

        $this->assertCount(1, $processes);
        $this->assertSame('5678', $processes[0]->getPid());
        $this->assertSame('/usr/sbin/mysqld', $processes[0]->getName());
        $this->assertSame('mysql', $processes[0]->getUsername());
    }

    public function testParseHandlesEmptyOutput(): void
    {
        $output = '  PID TIME     SZ USER    SESS COMMAND';

        /** @var \Craftpip\ProcessHandler\Process[] $processes */
        $processes = $this->parseMethod->invoke($this->driver, $output);

        $this->assertCount(0, $processes);
    }

    public function testParseReturnsProcessObjects(): void
    {
        $output = "  PID TIME     SZ USER    SESS COMMAND\n"
            . '    1 00:00:00 128 root      ? sh';

        /** @var \Craftpip\ProcessHandler\Process[] $processes */
        $processes = $this->parseMethod->invoke($this->driver, $output);

        $this->assertInstanceOf(\Craftpip\ProcessHandler\Process::class, $processes[0]);
    }
}
