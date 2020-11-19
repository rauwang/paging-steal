<?php
/**
 * author: rauwang
 * email: hi.rauwang@gmail.com
 * description:
 */

namespace Rauwang\Exception;

class DriverClassIniException extends \Exception
{
    public function __construct(string $driverClass) {
        $message = '没有配置['. $driverClass .']类';
        parent::__construct($message);
    }
}
