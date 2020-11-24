<?php
/**
 * author: rauwang
 * email: hi.rauwang@gmail.com
 * description:
 */

namespace Rauwang\PagingSteal\Controller;

use Rauwang\PagingSteal\Driver\Repositories\StealDataPage;
use Rauwang\PagingSteal\Exception\CrossGenerationException;
use Rauwang\PagingSteal\Exception\DriverClassException;
use Rauwang\PagingSteal\Exception\DriverClassIniException;

class StealDataPageController
{
    /**
     * @var string & StealDataPage
     */
    private static $stealDataPageClass;

    /**
     * @param string $stealDataPageClass
     *
     * @throws DriverClassException
     * @throws DriverClassIniException
     */
    public static function initStealDataPageClass(string $stealDataPageClass) : void {
        if (empty($stealDataPageClass[0]))
            throw new DriverClassIniException(StealDataPage::class);
        if (!is_subclass_of($stealDataPageClass, StealDataPage::class))
            throw new DriverClassException($stealDataPageClass, StealDataPage::class);
        self::$stealDataPageClass = $stealDataPageClass;
    }

    /**
     * StealDataPageController constructor.
     * @throws DriverClassIniException
     */
    public function __construct() {
        if (empty(self::$stealDataPageClass))
            throw new DriverClassIniException(StealDataPage::class);
    }

    private function urlHash(string $url) : string {
        return hash('md4', $url);
    }

    public function isStole(string $url) : bool {
        return self::$stealDataPageClass::exists($this->urlHash($url));
    }

    public function findDataPage(string $url) : StealDataPage {
        return self::$stealDataPageClass::find($this->urlHash($url));
    }

    public function existsWithBreakpoint(int $breakpointId, int $generation) : bool {
        return self::$stealDataPageClass::existsWithBreakpoint($breakpointId, $generation);
    }

    /**
     * @param string $url
     * @param int    $breakpointId
     * @param int    $generation
     *
     * @throws CrossGenerationException
     */
    public function create(string $url, int $breakpointId, int $generation) : void {
        if ($this->isStole($url)) {
            $dataPage = $this->findDataPage($url);
            if ($dataPage->getGeneration() === $generation) return;
            throw new CrossGenerationException();
        }
        self::$stealDataPageClass::create($breakpointId, $generation, $this->urlHash($url), $url);
    }
}
