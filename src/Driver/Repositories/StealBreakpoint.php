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

    abstract public static function create(array $params) : self ;

    abstract public static function fetchBreakpointCount(int $targetId, int $generation) : int ;

    protected function __construct() { }

    abstract public function getId() : int ;

    abstract public function getUrl() : string ;
}
