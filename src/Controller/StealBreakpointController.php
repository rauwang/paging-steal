<?php
/**
 * author: rauwang
 * email: hi.rauwang@gmail.com
 * description:
 */

namespace Rauwang\PagingSteal\Controller;

use Rauwang\PagingSteal\Driver\Repositories\StealBreakpoint;
use Rauwang\PagingSteal\Exception\DriverClassException;
use Rauwang\PagingSteal\Exception\DriverClassIniException;

class StealBreakpointController
{
    /**
     * @var string & StealBreakpoint
     */
    private static $stealBreakpointClass;

    /**
     * @param string $stealBreakpointClass
     *
     * @throws DriverClassException
     * @throws DriverClassIniException
     */
    public static function initStealBreakpointClass(\string $stealBreakpointClass) : \void {
        if (empty($stealBreakpointClass[0]))
            throw new DriverClassIniException(StealBreakpoint::class);
        if (!is_subclass_of($stealBreakpointClass, StealBreakpoint::class))
            throw new DriverClassException($stealBreakpointClass, StealBreakpoint::class);
        self::$stealBreakpointClass = $stealBreakpointClass;
    }

    /**
     * StealBreakpointController constructor.
     * @throws DriverClassIniException
     */
    public function __construct() {
        if (empty(self::$stealBreakpointClass))
            throw new DriverClassIniException(StealBreakpoint::class);
    }

    public function create(\int $targetId, \int $generation, \string $url) : \int {
        $breakpoint = self::$stealBreakpointClass::create($targetId, $generation, $url);
        return $breakpoint->getId();
    }

    public function hasBreakpoint(\int $targetId, \int $currentGeneration) : \bool {
        return self::$stealBreakpointClass::exists($targetId, $currentGeneration);
    }

    public function countBreakpointLength(\int $targetId, \int $generation) : \int {
        return self::$stealBreakpointClass::fetchBreakpointCount($targetId, $generation);
    }

    public function findOriginBreakpointUrl(\int $targetId, \int $generation) : \string {
        $breakpoint = self::$stealBreakpointClass::findLastCreateBreakpoint($targetId, $generation);
        return $breakpoint->getUrl();
    }
}
