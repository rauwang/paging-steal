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

    abstract public static function create(array $params) : self ;

    protected function __construct() { }

    abstract public function getBreakpointId() : int ;

    abstract public function getGeneration() : int ;
}
