<?php

namespace Craftpip\ProcessHandler\Drivers;

interface DriversInterface
{
    /**
     * @return \Craftpip\ProcessHandler\Process[]
     */
    public function getAllProcesses();

    /**
     * @param $pid
     *
     * @return \Craftpip\ProcessHandler\Process[]
     */
    public function getProcessByPid($pid);
}
