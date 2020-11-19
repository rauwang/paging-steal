<?php
/**
 * author: rauwang
 * email: hi.rauwang@gmail.com
 * description:
 */

namespace Rauwang;

use Rauwang\Controller\StealBreakpointController;
use Rauwang\Controller\PagingStealController;
use Rauwang\Controller\StealDataPageController;
use Rauwang\Controller\StealTargetController;
use Rauwang\Exception\DriverClassException;
use Rauwang\Exception\DriverClassIniException;

class PagingSteal
{
    /**
     * @var PagingStealController
     */
    private $pagingController;

    /**
     * @var StealBreakpointController
     */
    private static $breakpointController;

    private function __construct(PagingStealController $pagingController) {
        $this->pagingController = $pagingController;
    }

    /**
     * @param array $ini
     *
     * @throws DriverClassException
     * @throws DriverClassIniException
     */
    public static function config (array $ini) : void {
        StealDataPageController::initStealDataPageClass($ini);
        StealBreakpointController::initStealBreakpointClass($ini);
        StealTargetController::initStealTargetClass($ini);
    }

    /**
     * @param array $ini
     *
     * @return PagingSteal
     * @throws DriverClassException
     * @throws DriverClassIniException
     * @throws \Exception
     */
    public static function build(array $ini) : self {
        if (empty(self::$breakpointController))
            self::$breakpointController = new StealBreakpointController();
        return new self(PagingStealController::build($ini));
    }

    public function steal() {
        try {
            $this->pagingController->handlePaging(self::$breakpointController);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
