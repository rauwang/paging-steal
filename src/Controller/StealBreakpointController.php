<?php
/**
 * author: rauwang
 * email: hi.rauwang@gmail.com
 * description:
 */

namespace Rauwang\Controller;

use Rauwang\Driver\Repositories\StealBreakpoint;
use Rauwang\Exception\DriverClassException;
use Rauwang\Exception\DriverClassIniException;

class StealBreakpointController
{
    /**
     * @var string & StealBreakpoint
     */
    private static $stealBreakpointClass;

    /**
     * @param array $ini [配置参数]
     *
     * @throws DriverClassException
     * @throws DriverClassIniException
     */
    public static function initStealBreakpointClass(array $ini) : void {
        if (empty($ini['StealBreakpoint']))
            throw new DriverClassIniException(StealBreakpoint::class);
        $stealBreakpointClass = $ini['StealBreakpoint'];
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

    public function create(string $url, int $targetId, int $generation) : int {
        $breakpoint = self::$stealBreakpointClass::create([
            'target_id' => $targetId,
            'generation' => $generation,
            'hash_url' => hash('md4', $url),
            'url' => $url,
        ]);
        return $breakpoint->getId();
    }

    public function hasBreakpoint(int $targetId, int $currentGeneration) : bool {
        return self::$stealBreakpointClass::exists($targetId, $currentGeneration);
    }

    public function countBreakpointLength(int $targetId, int $generation) : int {
        return self::$stealBreakpointClass::fetchBreakpointCount($targetId, $generation);
    }

    public function findOriginBreakpointUrl(int $targetId, int $generation) : string {
        $breakpoint = self::$stealBreakpointClass::findLastCreateBreakpoint($targetId, $generation);
        return $breakpoint->getUrl();
    }
}
