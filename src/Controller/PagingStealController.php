<?php
/**
 * author: rauwang
 * email: hi.rauwang@gmail.com
 * description:
 */

namespace Rauwang\PagingSteal\Controller;

use Rauwang\PagingSteal\Driver\PagingSteal;
use Rauwang\PagingSteal\Exception\CrossGenerationException;
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
        if (!$this->isStoleWithFirstNode()) return false;
        return $this->isStoleWithLastNode();
    }

    private function isStoleWithFirstNode() : bool {
        return $this->dataPageController->isStole($this->stealPaging->getFirstNodeUrl());
    }

    private function isStoleWithLastNode() : bool {
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
     * @return int [断点id，0表示没有断点id]
     * @throws OverLastPageException
     */
    private function locateToBreakpoint(StealBreakpointController $breakpointController) {
        $targetId = $this->targetController->getId();
        $generation = $this->targetController->getGeneration();

        $lastBreakpoint = $breakpointController->findLastCreateBreakpoint($targetId, $generation);
        $originBreakpointUrl = $lastBreakpoint->getUrl();
        $breakpointId = $lastBreakpoint->getId();
        $this->setUrl($originBreakpointUrl);

        // 判断并计算断点
        $isStoleWithLastNode = $this->isStoleWithLastNode();
        $isStoleWithFirstNode = $this->isStoleWithFirstNode();
        if ($isStoleWithFirstNode && $isStoleWithLastNode) { // 当前分页已爬取过
            // 若当前分页没有跨世代，则定位到现断点的位置
            if (!$this->isEqualGenerationWithDataPagesInCurrentPaging($breakpointController)) return $breakpointId;
            if ($this->currentUrl == $originBreakpointUrl) return $breakpointId; // 当节点的世代相同，且没有发生偏移时
            return 0;
        }
        // 头节点已爬取，尾节点未爬取，则返回当前断点id
        if ($isStoleWithFirstNode) return $breakpointId;

        $lastDataPage = $this->dataPageController->findDataPage($this->stealPaging->getLastNodeUrl());
        if ($isStoleWithLastNode) { // 头节点未爬取，尾节点已爬取
            $offset = $breakpointController->countLengthAfterThisBreakpointId($lastDataPage->getBreakpointId(), $targetId, $generation);
            $this->setUrl($this->nextUrl($offset));
            return 0;
        }
        // 根据断点记录定位到的当前分页，未爬取过的情况下（头尾节点都未爬取）
        // 判断该断点是否存在已爬的数据页
        // 若存在已爬的数据页，则以断点的长度为偏移值，递进到已爬的分页，并判断其节点的世代情况，返回断点id
        if ($this->dataPageController->existsWithBreakpoint($lastDataPage->getBreakpointId(), $generation)) {
            $offset = $breakpointController->countBreakpointLength($targetId, $generation);
            while (true) {
                $this->setUrl($this->nextUrl($offset));
                if ($this->isStole()) break;
            }
            if ($this->isEqualGenerationWithDataPagesInCurrentPaging($breakpointController)) return 0;
            $dataPage = $this->dataPageController->findDataPage($this->stealPaging->getFirstNodeUrl());
            return $dataPage->getBreakpointId();
        }
        // 若不存在已爬的数据页，则从当前位置开始爬取
        return $breakpointId;
    }

    /**
     * 检查当前分页中的数据页的世代编号是否一致
     *
     * @param StealBreakpointController $breakpointController
     *
     * @return bool
     * @throws OverLastPageException
     */
    private function isEqualGenerationWithDataPagesInCurrentPaging(StealBreakpointController $breakpointController) : bool {
        $firstDataPage = $this->dataPageController->findDataPage($this->stealPaging->getFirstNodeUrl());
        $lastDataPage = $this->dataPageController->findDataPage($this->stealPaging->getLastNodeUrl());
        if ($firstDataPage->getGeneration() === $lastDataPage->getGeneration()) {
            $offset = $breakpointController->countLengthAfterThisBreakpointId($lastDataPage->getBreakpointId(), $this->targetController->getId(), $this->targetController->getGeneration());
            if (0 < $offset) $this->setUrl($this->nextUrl($offset));
            return true;
        } return false;
    }

    /**
     * 执行分页操作
     *
     * @param StealBreakpointController $breakpointController
     *
     * @throws \Exception
     */
    public function pagingHandler(StealBreakpointController $breakpointController) : void {
        // 定位到分页执行的初始位置
        $targetId = $this->targetController->getId();
        $breakpointId = 0;
        if ($breakpointController->hasBreakpoint($targetId, $this->targetController->getGeneration())) {
            $breakpointId = $this->locateToBreakpoint($breakpointController); // 定位到现断点的位置
        } else $this->resetToFirstPage();

        // 循环执行分页
        while (true) {
            $generation = $this->targetController->getGeneration();
            if (0 === $breakpointId)
                $breakpointId = $breakpointController->create($targetId, $generation, $this->currentUrl);
            $this->paging($breakpointId, $generation);
            $breakpointId = 0;
        }
    }

    /**
     * @param int $breakpointId
     * @param     $generation
     *
     * @throws \Exception
     */
    private function paging(int $breakpointId, $generation) : void {
        try {
            $dataPageUrlList = $this->stealPaging->fetchDataPageUrlList();
            $this->dataPageIterator($dataPageUrlList, $breakpointId, $generation);
            $this->setUrl($this->nextUrl());
        } catch (OverLastPageException $e) {
            $this->nextGeneration();
        } catch (CrossGenerationException $e) {
            $this->nextGeneration();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 数据页链接迭代器
     *
     * @param array $dataPageUrlList
     * @param int   $breakpointId
     * @param int   $generation
     *
     * @throws CrossGenerationException
     */
    private function dataPageIterator(array $dataPageUrlList, int $breakpointId, int $generation) : void {
        foreach ($dataPageUrlList as $dataPageUrl) {
            $this->dataPageController->create($dataPageUrl, $breakpointId, $generation);
        }
    }
}
