<?php

require_once "vendor/autoload.php";

$processHandler = new \Craftpip\ProcessHandler\ProcessHandler();
$process = new \Symfony\Component\Process\Process(['wait 10']);
$process->start();
$pid = $process->getPid();
echo $pid;
echo "\n";
$process = $processHandler->getProcess($pid);
print_r($process);
print_r($process->isRunning());
print_r($process->getWindowTitle());

