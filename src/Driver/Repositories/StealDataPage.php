<?php
/**
 * author: rauwang
 * email: hi.rauwang@gmail.com
 * description:
 */

namespace Rauwang\PagingSteal\Driver\Repositories;

abstract class StealDataPage
{
    abstract public static function exists(string $urlHash) : bool ;

    abstract public static function find(string $urlHash) : self ;

    abstract public static function create(int $breakpointId, int $generation, string $urlHash, string $url) : self ;

    /**
     * 根据断点id和世代编号，判断是否有存在的数据页
     *
     * @param int $breakpointId
     * @param int $generation
     *
     * @return bool
     */
    abstract public static function existsWithBreakpoint(int $breakpointId, int $generation) : bool ;

    protected function __construct() { }

    abstract public function getBreakpointId() : int ;

    abstract public function getGeneration() : int ;
}
