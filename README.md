# process-handler
Get list of running processes by name or pid, supports windows and unix
  
I struggled to find a library that returns the processes list for the operating system.
My use case was to find if my spawned process was running or not.

## Usage


```php
use \Craftpip\ProcessHandler\ProcessHandler;
use \Symfony\Component\Process\Process;

// Creating a process and checking if a process by its pid exists.
$processHandler = new ProcessHandler();
$process = new Process('ls');
$process->start();
$pid = $process->getPid();
$processes = $processHandler->api->getProcessByPid($pid);
if(count($processes)){
    // process exists.
}else{
    // process was not found.
}
```
