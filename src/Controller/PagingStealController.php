<?php
/**
 * author: rauwang
 * email: hi.rauwang@gmail.com
 * description:
 */

namespace Rauwang\PagingSteal\Controller;

use Rauwang\PagingSteal\Driver\PagingSteal;
use Rauwang\PagingSteal\Exception\DriverClassException;
use Rauwang\PagingSteal\Exception\DriverClassIniException;
use Rauwang\PagingSteal\Exception\OverLastPageException;

class PagingStealController
{
    /**
     * @var string
     */
    private $currentUrl;

    /**
     * @var PagingSteal
     */
    private $stealPaging;

    /**
     * @var StealDataPageController
     */
    private $dataPageController;

    /**
     * @var StealTargetController
     */
    private $targetController;

    private static $stealPagingControllerCache = [];

    /**
     * @param string $pagingStealClass
     *
     * @return PagingStealController
     * @throws DriverClassException
     * @throws DriverClassIniException
     * @throws \Exception
     */
    public static function build(string $pagingStealClass) : self {
        if (empty($pagingStealClass[0]))
            throw new DriverClassIniException(PagingSteal::class);
        if (!is_subclass_of($pagingStealClass, PagingSteal::class))
            throw new DriverClassException($pagingStealClass, PagingSteal::class);
        if (empty(self::$stealPagingControllerCache[$pagingStealClass])) {
            self::$stealPagingControllerCache[$pagingStealClass] = new self(new $pagingStealClass);
        } return self::$stealPagingControllerCache[$pagingStealClass];
    }

    /**
     * PagingController constructor.
     *
     * @param PagingSteal $stealPaging
     *
     * @throws \Exception
     */
    private function __construct(PagingSteal $stealPaging) {
        $this->stealPaging = $stealPaging;
        $this->targetController = new StealTargetController($this->stealPaging::getFirstPageUrl());
        $this->dataPageController = new StealDataPageController();
    }

    public function setUrl(string $url) : void {
        $this->currentUrl = $url;
        $this->stealPaging->setUrl($url);
    }

    public function isStole() : bool {
        if (!$this->dataPageController->isStole($this->stealPaging->getFirstNodeUrl())) return false;
        return $this->dataPageController->isStole($this->stealPaging->getLastNodeUrl());
    }

    /**
     * @param int $offset
     *
     * @return string
     * @throws OverLastPageException
     */
    public function nextUrl(int $offset=1) : string {
        $nextUrl = $this->stealPaging->nextUrl($offset);
        if (empty($nextUrl[0]))
            throw new OverLastPageException();
        return $nextUrl;
    }

    public function nextGeneration() : void {
        $this->targetController->crossGeneration(1);
        $this->resetToFirstPage();
    }

    public function resetToFirstPage() : void {
        $this->setUrl($this->stealPaging::getFirstPageUrl());
    }

    /**
     * 定位到断点位置
     *
     * @param StealBreakpointController $breakpointController
     *
     * @throws OverLastPageException
     */
    private function locateToBreakpoint(StealBreakpointController $breakpointController) : void {
        $targetId = $this->targetController->getId();
        $generation = $this->targetController->getGeneration();
        $breakpointCount = $breakpointController->countBreakpointLength($targetId, $generation);
        $breakpointUrl = $breakpointController->findOriginBreakpointUrl($targetId, $generation);
        while (true) { // 定位到(原)断点的位置
            $this->setUrl($breakpointUrl);
            if ($this->isStole()) break;
            $breakpointUrl = $this->nextUrl($breakpointCount);
        }
        // 根据分页最后一节点，确认该断点是占断点长度的哪个位置
        $dataPage = $this->dataPageController->findDataPage($this->stealPaging->getLastNodeUrl());
        // 以此算出偏移到实际断点位置的偏移值
        $offset = $breakpointController->countLengthAfterThisBreakpointId($dataPage->getBreakpointId(), $targetId, $generation);
        $this->setUrl($this->nextUrl($offset));
        // 判断断点分页是否已全部爬取过，如果是，则分页+1
        if ($this->isStole()) $this->setUrl($this->nextUrl(1));
    }

    /**
     * 执行分页操作
     *
     * @param StealBreakpointController $breakpointController
     *
     * @throws \Exception
     */
    public function handlePaging(StealBreakpointController $breakpointController) : void {
        if ($breakpointController->hasBreakpoint($this->targetController->getId(), $this->targetController->getGeneration())) {
            $this->locateToBreakpoint($breakpointController);
        } else {
            $this->resetToFirstPage();
        }
        while (true) {
            if ($this->isStole()) {
                $lastNodeUrl = $this->stealPaging->getLastNodeUrl();
                $dataPage = $this->dataPageController->findDataPage($lastNodeUrl);
                // 当前分页（最后一个节点）的世代编号，是否与当前的世代编号一致？
                if ($dataPage->getGeneration() !== $this->targetController->getGeneration())
                    break;
                $this->nextGeneration();
            } else {
                $breakpointId = $breakpointController->create($this->targetController->getId(), $this->targetController->getGeneration(), $this->currentUrl);
                $dataPageUrlList = $this->stealPaging->fetchDataPageUrlList();
                $this->dataPageController->create($dataPageUrlList, $breakpointId, $this->targetController->getGeneration());
                try {
                    $this->setUrl($this->nextUrl());
                } catch (OverLastPageException $e) {
                    $this->nextGeneration();
                }
            }
        }
    }
}
