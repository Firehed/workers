<?php

namespace Firehed\Workers\Examples;

require_once __DIR__.'/../vendor/autoload.php';

use Firehed\Workers\Worker;
use Firehed\Workers\WorkerManager;

class ReverseWorker implements Worker
{
    public function getName(): string
    {
        return 'reverse';
    }

    public function getRunLimit(): int
    {
        return Worker::RUN_LIMIT_UNLIMITED;
    }

    public function getNice(): int
    {
        return 0;
    }

    public function getProcessTitle(): string
    {
        return 'title';
    }

    public function work(): bool
    {
        $payload = 'Some string I fetched from a queue';
        printf("Starting work the the following 'fetched' payload: %s\n", $payload);
        $result = strrev($payload);

        // 'Act' on the result
        printf("Result: %s\n", $result);

        // This is just for example purposes; workers shouldn't normally sleep
        sleep(1);

        // Indicate that work was performed
        return true;
    }
}

// Do any configuration of the worker you need
$worker = new ReverseWorker();

$manager = new WorkerManager();
$manager->addWorker($worker);

echo <<<'TEXT'
This example starts a 'reverse' worker in the foreground. Press ^C to exit.


TEXT;

$manager->runInForeground($worker->getName());
// In a more complex example, you may have multiple workers configired in the
// same script. Rather than explicitly running a given worker like above, you
// may determine which of the available workers to run from a CLI argument
// ($argv) or environment variable.
