<?php
declare(strict_types=1);

namespace Firehed\Workers;

interface Worker
{
    const NICE_DEFAULT = 0;

    const RUN_LIMIT_UNLIMITED = 0;

    public function getName(): string;

    public function getNice(): int;

    /**
     * Desired process title (for `top`, etc)
     *
     * @return string
     */
    public function getProcessTitle(): string;

    public function getRunLimit(): int;

    /**
     * Do one unit if work. How "one unit" is defined is up to the user, but
     * the worker must not attempt to define its own run loop.
     *
     * @return bool whether or not any work was attempted (e.g. false if there
     * was nothing to do)
     */
    public function work(): bool;
}
