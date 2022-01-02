<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Codeception\EventDispatcher\Subscriber;

use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\ConsideredRiskySubscriber;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestConsideredRiskySubscriber extends Subscriber implements ConsideredRiskySubscriber
{
    public function notify(ConsideredRisky $event): void
    {
        $this->eventDispatcher()->testConsideredRisky($event);
    }
}
