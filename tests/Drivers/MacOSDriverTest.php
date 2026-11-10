<?php

namespace Craftpip\Tests\Drivers;

use Craftpip\ProcessHandler\Drivers\MacOS;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class MacOSDriverTest extends TestCase
{
    private MacOS $driver;
    private \ReflectionMethod $parseMethod;

    protected function setUp(): void
    {
        $this->driver = new MacOS();
        $reflection = new ReflectionClass($this->driver);
        $this->parseMethod = $reflection->getMethod('parse');
        $this->parseMethod->setAccessible(true);
    }

    public function testParseReturnsProcessesFromPsOutput(): void
    {
        $output = "  PID     TIME    RSS USER    SESS ARGS\n"
            . "    1 00:03:12 12800 root    ?    /sbin/launchd\n"
            . ' 1234 00:01:23 20480 www     1    nginx: worker process';

        /** @var \Craftpip\ProcessHandler\Process[] $processes */
        $processes = $this->parseMethod->invoke($this->driver, $output);

        $this->assertCount(2, $processes);
        $this->assertSame('1', $processes[0]->getPid());
        $this->assertSame('/sbin/launchd', $processes[0]->getName());
        $this->assertSame('root', $processes[0]->getUsername());
        $this->assertSame('12800 KB', $processes[0]->getMemUsed());
        $this->assertSame('RUNNING', $processes[0]->getStatus());

        $this->assertSame('1234', $processes[1]->getPid());
        $this->assertSame('nginx: worker process', $processes[1]->getName());
    }

    public function testParseSkipsHeaderLine(): void
    {
        $output = "  PID     TIME    RSS USER    SESS ARGS\n"
            . '    1 00:03:12 12800 root    ?    /sbin/launchd';

        /** @var \Craftpip\ProcessHandler\Process[] $processes */
        $processes = $this->parseMethod->invoke($this->driver, $output);

        $this->assertCount(1, $processes);
    }

    public function testParseHandlesMultipleSpacesInOutput(): void
    {
        // macOS ps output can have variable whitespace
        $output = "  PID     TIME    RSS USER    SESS ARGS\n"
            . '    1  00:03:12  12800  root    ?    /sbin/launchd';

        /** @var \Craftpip\ProcessHandler\Process[] $processes */
        $processes = $this->parseMethod->invoke($this->driver, $output);

        $this->assertCount(1, $processes);
        $this->assertSame('1', $processes[0]->getPid());
        $this->assertSame('/sbin/launchd', $processes[0]->getName());
    }

    public function testParseHandlesEmptyOutput(): void
    {
        $output = '  PID     TIME    RSS USER    SESS ARGS';

        /** @var \Craftpip\ProcessHandler\Process[] $processes */
        $processes = $this->parseMethod->invoke($this->driver, $output);

        $this->assertCount(0, $processes);
    }

    public function testParseReturnsProcessObjects(): void
    {
        $output = "  PID     TIME    RSS USER    SESS ARGS\n"
            . '    1 00:00:00 128 root    ?    sh';

        /** @var \Craftpip\ProcessHandler\Process[] $processes */
        $processes = $this->parseMethod->invoke($this->driver, $output);

        $this->assertInstanceOf(\Craftpip\ProcessHandler\Process::class, $processes[0]);
    }
}
