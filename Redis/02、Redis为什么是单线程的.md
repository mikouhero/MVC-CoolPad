# Redis 为什么是单线程的？

### 官方解释

> Redis是基于内存的操作，CPU不会成为瓶颈。Redis的瓶颈最大可能是机器内存的大小或者带宽，既然单线程容易实现，CPU不会成为瓶颈 那么就采用单线程的方案。这里的单线程是处理网络请求的模块是单线程 其他模块不一定是单线程

### 采用单线程的优势

> 1、Redis 项目的代码更加清晰 处理逻辑更加简单  
> 2、不用考虑多个线程修改数据的情况 修改数据时不用加锁 解锁 也不会出现死锁的问题，导致性能消耗  
> 3、不存在多线程或者多进程导致的切换的一些性能消耗

### 采用单线程的缺点 

> 无法充分发挥多核机器的优势 ，可以通过机器上启动多个Redis实例来利用资源
 