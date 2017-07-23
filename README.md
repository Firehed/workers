# Workers

A library to manage worker processes in a run loop.

## Worker Manager

At the heart of any run loop is the Worker Manager.
It will track execution count, set up basic POSIX signal handling, and other basic infrastructure.


## Worker Class
The actual tasks are performed by a Worker class - anything that implements `Firehed\Workers\Worker`.

### `getName(): string`
This is an identifer for the type of worker.
`return __CLASS__;` is a sensible implmentation, but may result in more work that desired when specifying the worker if running in the foreground.

### `getNice(): int`
The `nice` level for the process, which controls the overall system priority.
A number between -20 and 20.
-20 is the highest priority (don't go this high, or you could make the system unresponsive); +20 is the lowest priority.
Return `Worker::NICE_DEFAULT` (0) to leave the priority unaffected.

### `getProcessTitle(): string`
If this value is not empty, the manager will attempt to update the process title that's visible in utilities like `top`.
Does not work on all operating systems.

### `getRunLimit(): int`
Exit the run loop after this many iterations.
Useful to cleanly exit workers that may consume more memory than desirable.
Return `Worker::RUN_LIMIT_UNLIMITED` to not enforce a limit.

Note that implementations should avoid relying this, although it's well understood that PHP code often leaves resources hanging when it was originally designed around the web's shared nothing "work once and die" model.

### `work(): bool`
Perform the actual work.
This should fetch a single job and perform it.
Implementations may read a row out of a database, grab a job out of a work queue, etc.

The worker's `work()` method should only return `false` if no work was attempted (nothing in the queue, etc).
It should still return `true` if work was attempted and failed.

## Using in Docker: Foreground Worker

Call `WorkerManager->runInForeground($name)` to run a single worker in the foreground.
This is ideal for applications designed around containerization (Docker) where some higher-level process manager already exists.

It is highly recommended to create a single worker script and corresponding container, and control which worker to run with a CLI argument or environment variable.

