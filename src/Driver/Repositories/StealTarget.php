<?php
/**
 * author: rauwang
 * email: hi.rauwang@gmail.com
 * description:
 */

namespace Rauwang\PagingSteal\Driver\Repositories;

abstract class StealTarget
{
    abstract public static function exists(string $urlHash) : bool ;

    abstract public static function find(string $urlHash) : self ;

    abstract public static function create(string $url, string $urlHash, int $generation) : self ;

    protected function __construct() { }

    abstract public function getId() : int ;

    abstract public function getGeneration() : int ;

    abstract public function updateGeneration(int $generation) : self ;
}
