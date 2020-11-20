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
     * @param array $ini [配置参数]
     *
     * @throws DriverClassException
     * @throws DriverClassIniException
     */
    public static function initStealTargetClass(array $ini) : void {
        if (empty($ini['StealTarget']))
            throw new DriverClassIniException(StealTarget::class);
        $stealTargetClass = $ini['StealTarget'];
        if (!is_subclass_of($stealTargetClass, StealTarget::class))
            throw new DriverClassException($stealTargetClass, StealTarget::class);
        self::$stealTargetClass = $stealTargetClass;
    }

    /**
     * TargetController constructor.
     *
     * @param string $urlHost
     *
     * @throws \Exception
     */
    public function __construct(string $urlHost) {
        if (empty(self::$stealTargetClass))
            throw new \Exception('$stealTargetClass不能为空');

        $hashKey = hash('md4', $urlHost);
        if (self::$stealTargetClass::exists($hashKey)) {
            $this->stealTarget = self::$stealTargetClass::find($hashKey);
            return;
        }
        // 创建对象
        $this->stealTarget = self::$stealTargetClass::create([
            'hash_url_host' => $hashKey,
            'url_host' => $urlHost,
            'generation' => 1,
        ]);
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
