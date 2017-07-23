<?php
declare(strict_types=1);

namespace Firehed\Workers;

use OutOfBoundsException;

class WorkerManager
{
    /** @var Worker[] */
    private $workers;

    // Worker variables
    /** @var int */
    private $runCount = 0;

    /** @var int */
    private $runLimit;

    /** @var bool */
    private $stop = false;

    public function __construct()
    {
        if (function_exists('pcntl_async_signals')) {
            pcntl_async_signals(true);
        } else {
        }
    }

    /**
     * Register a worker to manage
     *
     * @param Worker $worker the worker to manage
     *
     * @return $this
     */
    public function addWorker(Worker $worker): self
    {
        $this->workers[$worker->getName()] = $worker;

        return $this;
    }

    /**
     * Run a worker with the provided name in the foreground
     *
     * @param string $workerName The worker name (corresponds to
     * Worker->getName()
     *
     * @throws OutOfBoundsException if a worker with that name does not exist
     */
    public function runInForeground(string $workerName)
    {
        if (!isset($this->workers[$workerName])) {
            throw new OutOfBoundsException('Invalid worker name');
        }

        $worker = $this->workers[$workerName];

        $this->runWorker($worker);
    }

    /**
     * Stops the run loop after the current unit of work is performed
     */
    public function stop()
    {
        $this->stop = true;
    }

    // -( Private methods )----------------------------------------------------

    /**
     * Sets up listeners for various POSIX signals
     */
    private function installSignals()
    {
        $handler = function (int $signal) {
            $this->signal($signal);
        };
        pcntl_signal(SIGTERM, $handler);
        pcntl_signal(SIGINT, $handler);
    }

    /**
     * Configures the process and manages the run loop
     */
    private function runWorker(Worker $worker)
    {
        @cli_set_process_title($worker->getProcessTitle());
        proc_nice($worker->getNice());
        $this->runLimit = $worker->getRunLimit();
        $this->installSignals();
        while ($this->shouldWork()) {
            if ($worker->work()) {
                $this->runCount++;
            }
        }
    }

    /**
     * Indicates if the next unit of work should be performed
     *
     * @return bool
     */
    private function shouldWork(): bool
    {
        if ($this->stop) {
            return false;
        }

        return $this->runLimit === Worker::RUN_LIMIT_UNLIMITED
            || $this->runCount < $this->runLimit;
    }

    /**
     * System signal handler (see ::installSignals)
     *
     * @param int $signal The signal (see `man signal`)
     */
    private function signal(int $signal)
    {
        switch ($signal) {
            case SIGINT:
            case SIGTERM:
                $this->stop();
                break;
            default:
                echo "Got something else";
                break;
        }
    }
}
