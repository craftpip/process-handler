<?php

namespace Craftpip\Tests\Drivers;

use Craftpip\ProcessHandler\Drivers\Windows;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class WindowsDriverTest extends TestCase
{
    private Windows $driver;
    private \ReflectionMethod $parseOutputMethod;

    protected function setUp(): void
    {
        $this->driver = new Windows();
        $reflection = new ReflectionClass($this->driver);
        $this->parseOutputMethod = $reflection->getMethod('parseOutput');
        $this->parseOutputMethod->setAccessible(true);
    }

    public function testParseOutputReturnsProcessesFromTasklistOutput(): void
    {
        // Sample tasklist /V output (header + separator + data rows)
        $output = "Image Name                     PID Session Name        Session#    Mem Usage Status          User Name                      CPU Time    Window Title\n"
            . "========================= ======== ================ =========== ============ ============= ================================ ============ ============\n"
            . "System Idle Process              0 Services                   0          8 K Unknown         NT AUTHORITY\\SYSTEM               14:51:17 N/A\n"
            . 'System                           4 Services                   0      1,404 K Running         NT AUTHORITY\\SYSTEM               0:03:44 N/A';

        /** @var \Craftpip\ProcessHandler\Process[] $processes */
        $processes = $this->parseOutputMethod->invoke($this->driver, $output);

        $this->assertCount(2, $processes);
        $this->assertSame('System Idle Process', $processes[0]->getName());
        $this->assertSame('0', $processes[0]->getPid());
        $this->assertSame('Services', $processes[0]->getSessionName());
        $this->assertSame('8 K', $processes[0]->getMemUsed());
        $this->assertSame('Unknown', $processes[0]->getStatus());
        $this->assertSame('NT AUTHORITY\\SYSTEM', $processes[0]->getUsername());

        $this->assertSame('System', $processes[1]->getName());
        $this->assertSame('4', $processes[1]->getPid());
    }

    public function testParseOutputHandlesEmptyOutput(): void
    {
        $output = "Image Name                     PID Session Name        Session#    Mem Usage Status          User Name                      CPU Time    Window Title\n"
            . '========================= ======== ================ =========== ============ ============= ================================ ============ ============';

        /** @var \Craftpip\ProcessHandler\Process[] $processes */
        $processes = $this->parseOutputMethod->invoke($this->driver, $output);

        $this->assertCount(0, $processes);
    }

    public function testParseOutputHandlesNoResults(): void
    {
        $output = 'INFO: No tasks are running which match the specified criteria.';

        /** @var \Craftpip\ProcessHandler\Process[] $processes */
        $processes = $this->parseOutputMethod->invoke($this->driver, $output);

        $this->assertCount(0, $processes);
    }

    public function testParseOutputReturnsProcessObjects(): void
    {
        $output = "Image Name                     PID Session Name        Session#    Mem Usage Status          User Name                      CPU Time    Window Title\n"
            . "========================= ======== ================ =========== ============ ============= ================================ ============ ============\n"
            . 'cmd.exe                        100 Console                    1      5,120 K Running         User                            0:00:01 N/A';

        /** @var \Craftpip\ProcessHandler\Process[] $processes */
        $processes = $this->parseOutputMethod->invoke($this->driver, $output);

        $this->assertInstanceOf(\Craftpip\ProcessHandler\Process::class, $processes[0]);
    }
}
