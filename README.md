简单地封装一个断点续爬的小插件，只需要继承3个抽象类和实现一个接口就可以了。
抽象类：
* StealTarget: 记录分页的信息；
* StealBreakpoint：记录断点的信息；
* StealDataPage：记录已爬节点的信息；
接口：
* PagingSteal：具体的分页操作接口；

调用：
```
PagingSteal::config([
    'StealTarget' => TestStealTarget::class,
    'StealBreakpoint' => TestStealBreakpoint::class,
    'StealDataPage' => TestStealDataPage::class,
]);
PagingSteal::build(['PagingSteal'=>TestPagingSteal::class])->steal();
```