<?php
/**
 * author: rauwang
 * email: hi.rauwang@gmail.com
 * description:
 */

namespace Rauwang\Controller;

use Rauwang\Driver\Repositories\StealDataPage;
use Rauwang\Exception\DriverClassException;
use Rauwang\Exception\DriverClassIniException;

class StealDataPageController
{
    /**
     * @var string & StealDataPage
     */
    private static $stealDataPageClass;

    /**
     * @param array $ini [配置参数]
     *
     * @throws DriverClassException
     * @throws DriverClassIniException
     */
    public static function initStealDataPageClass(array $ini) : void {
        if (empty($ini['StealDataPage']))
            throw new DriverClassIniException(StealDataPage::class);
        $stealDataPageClass = $ini['StealDataPage'];
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

    public function create(array $urlList, int $breakpointId, int $generation) : void {
        foreach ($urlList as $url) {
            if ($this->isStole($url)) continue;
            self::$stealDataPageClass::create([
                'breakpoint_id' => $breakpointId,
                'generation' => $generation,
                'hash_url' => $this->urlHash($url),
                'url' => $url,
                'status' => 0,
            ]);
        }
    }
}
