<?php
declare(strict_types=1);

namespace Firehed\Workers;

use OutOfBoundsException;

/**
 * @coversDefaultClass Firehed\Workers\WorkerManager
 * @covers ::<protected>
 * @covers ::<private>
 */
class WorkerManagerTest extends \PHPUnit\Framework\TestCase
{
    /** @covers ::__construct */
    public function testConstruct()
    {
        $wm = new WorkerManager();
        $this->assertInstanceOf(WorkerManager::class, $wm);
    }

    /** @covers ::runInForeground */
    public function testRunInForeground()
    {
        $worker = $this->createMock(Worker::class);
        $worker->method('getName')->willReturn('someWorkerName');
        $worker->method('getRunLimit')->willReturn(5);
        $worker->method('getNice')->willReturn(0);
        $worker->expects($this->exactly(5))
            ->method('work')
            ->willReturn(true);

        $wm = new WorkerManager();
        $wm->addWorker($worker);

        $wm->runInForeground($worker->getName());
    }

    /** @covers ::runInForeground */
    public function testRunInForegroundRejectsNonRegisteredWorkers()
    {
        $worker = $this->createMock(Worker::class);
        $worker->method('getName')->willReturn('someWorkerName');
        $worker->expects($this->never())->method('work');

        $wm = new WorkerManager();
        $wm->addWorker($worker);

        $this->expectException(OutOfBoundsException::class);
        $wm->runInForeground('someOtherName');
    }

    /**
     * @covers ::runInForeground
     * @covers ::stop
     */
    public function testRunInForegroundWithNoLimit()
    {
        $wm = new WorkerManager();
        $worker = new class($wm) implements Worker {
            private $i;
            private $wm;

            public function __construct(WorkerManager $wm)
            {
                $this->wm = $wm;
            }

            public function getName(): string
            {
                return 'someWorkerName';
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
                $this->i++;
                if ($this->i >= 100) {
                    $this->wm->stop();
                }
                return true;
            }

            public function getRunCount(): int
            {
                return $this->i;
            }
        };

        $wm->addWorker($worker);
        $wm->runInForeground($worker->getName());
        // This worker self-limits to 100 runs, and will call stop on the
        // injected manager. If this test "fails", it will probably infinte
        // loop.
        $this->assertSame(100, $worker->getRunCount());
    }
}
