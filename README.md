简单地封装一个断点续爬的小插件，只需要继承3个抽象类和实现一个接口就可以了。

抽象类是对三个数据模型的操作进行抽象；接口是对分页操作的方法进行抽象。



### 抽象类

* StealTarget: 记录分页的信息；
* StealBreakpoint：记录断点的信息；
* StealDataPage：记录已爬节点的信息；



### 接口

* PagingSteal：具体的分页操作接口；



### 实现抽象类和接口

* `TestStealTarget`实现`StealTarget`抽象类：

```php
use Rauwang\PagingSteal\Driver\Repositories\StealTarget;

class TestStealTarget extends StealTarget {
	// ...
}
```

* `TestStealDataPage`实现`StealDataPage`抽象类：

```php
use Rauwang\PagingSteal\Driver\Repositories\StealDataPage;

class TestStealDataPage extends StealDataPage {
    // ...
}
```

* `TestStealBreakpoint`实现`StealBreakpoint`抽象类：

```php
use Rauwang\PagingSteal\Driver\Repositories\StealBreakpoint;

class TestStealBreakpoint extends StealBreakpoint {
    // ...
}
```

* `PagingStealDemo1`实现`PagingSteal`接口：

```php
use Rauwang\PagingSteal\Driver\PagingSteal;

class PagingStealDemo1 implements PagingSteal {
    // ...
}
```



### 配置

```php
\Rauwang\PagingSteal\PagingSteal::init(
	TestStealTarget::class,
    TestStealBreakpoint::class,
    TestStealDataPage::class,
);
```



### 调用

```php
\Rauwang\PagingSteal\PagingSteal::build(PagingStealDemo1::class)->steal();
```

