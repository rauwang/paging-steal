<?php
/**
 * author: rauwang
 * email: hi.rauwang@gmail.com
 * description:
 */

namespace Rauwang\PagingSteal;

use Rauwang\PagingSteal\Controller\StealBreakpointController;
use Rauwang\PagingSteal\Controller\PagingStealController;
use Rauwang\PagingSteal\Controller\StealDataPageController;
use Rauwang\PagingSteal\Controller\StealTargetController;
use Rauwang\PagingSteal\Exception\DriverClassException;
use Rauwang\PagingSteal\Exception\DriverClassIniException;

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
     * @param string $stealTargetClass
     * @param string $stealBreakpointClass
     * @param string $stealDataPageClass
     *
     * @throws DriverClassException
     * @throws DriverClassIniException
     */
    public static function init (\string $stealTargetClass, \string $stealBreakpointClass, \string $stealDataPageClass) : \void {
        StealTargetController::initStealTargetClass($stealTargetClass);
        StealDataPageController::initStealDataPageClass($stealDataPageClass);
        StealBreakpointController::initStealBreakpointClass($stealBreakpointClass);
    }

    /**
     * @param string $pagingStealClass
     *
     * @return PagingSteal
     * @throws DriverClassException
     * @throws DriverClassIniException
     */
    public static function build(\string $pagingStealClass) : self {
        if (empty(self::$breakpointController))
            self::$breakpointController = new StealBreakpointController();
        return new self(PagingStealController::build($pagingStealClass));
    }

    /**
     * @throws \Exception
     */
    public function steal() : \void{
        $this->pagingController->handlePaging(self::$breakpointController);
    }
}
