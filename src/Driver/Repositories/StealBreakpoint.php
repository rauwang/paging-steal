<?php
/**
 * author: rauwang
 * email: hi.rauwang@gmail.com
 * description:
 */

namespace Rauwang\PagingSteal\Driver\Repositories;

abstract class StealBreakpoint
{
    abstract public static function exists(int $targetId, int $generation) : bool ;

    abstract public static function findLastCreateBreakpoint(int $targetId, int $generation) : self ;

    abstract public static function create(int $targetId, int $generation, string $url) : self ;

    abstract public static function fetchBreakpointCount(int $targetId, int $generation) : int ;

    /**
     * 计算当前断点之后的断点长度
     *
     * @param int $breakpointId
     * @param int $targetId
     * @param int $generation
     *
     * @return int
     */
    abstract public static function countLengthAfterThisBreakpointId(int $breakpointId, int $targetId, int $generation) : int ;

    protected function __construct() { }

    abstract public function getId() : int ;

    abstract public function getUrl() : string ;
}
