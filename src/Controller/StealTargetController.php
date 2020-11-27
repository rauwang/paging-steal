<?php
/**
 * author: rauwang
 * email: hi.rauwang@gmail.com
 * description:
 */

namespace Rauwang\PagingSteal\Controller;

use Rauwang\PagingSteal\Driver\Repositories\StealTarget;
use Rauwang\PagingSteal\Exception\DriverClassException;
use Rauwang\PagingSteal\Exception\DriverClassIniException;

class StealTargetController
{
    /**
     * @var string & StealTarget
     */
    private static $stealTargetClass;

    /**
     * @var StealTarget
     */
    private $stealTarget;

    /**
     * @param string $stealTargetClass
     *
     * @throws DriverClassException
     * @throws DriverClassIniException
     */
    public static function initStealTargetClass(string $stealTargetClass) : void {
        if (empty($stealTargetClass[0]))
            throw new DriverClassIniException(StealTarget::class);
        if (!is_subclass_of($stealTargetClass, StealTarget::class))
            throw new DriverClassException($stealTargetClass, StealTarget::class);
        self::$stealTargetClass = $stealTargetClass;
    }

    /**
     * TargetController constructor.
     *
     * @param string $url
     *
     * @throws \Exception
     */
    public function __construct(string $url) {
        if (empty(self::$stealTargetClass))
            throw new \Exception('$stealTargetClass不能为空');

        if (self::$stealTargetClass::exists($url)) {
            $this->stealTarget = self::$stealTargetClass::find($url);
            return;
        }
        // 创建对象
        $this->stealTarget = self::$stealTargetClass::create($url, 1);
    }

    /**
     * @return int [对象id]
     */
    public function getId() : int {
        return $this->stealTarget->getId();
    }

    /**
     * @return int [世代编号]
     */
    public function getGeneration() : int {
        return $this->stealTarget->getGeneration();
    }

    /**
     * 世代交替
     *
     * @param int $offset
     *
     * @return int
     */
    public function crossGeneration(int $offset) : int {
        $generation = $this->stealTarget->getGeneration() + $offset;
        $this->stealTarget->updateGeneration($generation);
        return $generation;
    }
}
