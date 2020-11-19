<?php
/**
 * author: rauwang
 * email: hi.rauwang@gmail.com
 * description:
 */

namespace Rauwang\Driver;

interface PagingSteal
{
    public static function getUrlHost() : string ;

    public static function getFirstPageUrl() : string ;

    public function setUrl(string $url) : void ;

    public function nextUrl(int $offset) : string ;

    public function getFirstNodeUrl() : string ;

    public function getLastNodeUrl() : string ;

    public function fetchDataPageUrlList() : array ;
}
