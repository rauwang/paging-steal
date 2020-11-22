<?php
/**
 * author: rauwang
 * email: hi.rauwang@gmail.com
 * description:
 */

namespace Rauwang\PagingSteal\Exception;

class DriverClassException extends \Exception
{
    public function __construct(string $className, string $driverClass) {
        $message = '"'. $className .'"没有实现或继承['. $driverClass .']类';
        parent::__construct($message);
    }
}
