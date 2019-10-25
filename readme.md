# <font color=#007ACC> 简单介绍</font>

使用php、swoole的event模块、pcntl扩展，实现单进程阻塞io模型，单进程epoll，多进程epoll，代码中有详细注释，以便学习使用

>## 单进程阻塞io

worker.php

>## 单进程epoll

worker_epoll.php，需要安装swoole扩展（pecl install swoole）

>## 多进程epoll

workers_epoll.php以及run.php，运行run.php，wokers_epoll.php为类，需要安装pcntl扩展，使用多进程

>## 压测

使用ab压测，单进程epoll和多进程epoll的每秒处理请求数相差不大，主要差别表现在p99处理的完成时间上

``` shell
# 多进程，开3个进程+  ab -n20000 -c100 -k
Concurrency Level:      100
Time taken for tests:   0.376 seconds
Complete requests:      20000
Failed requests:        0
Keep-Alive requests:    20000
Total transferred:      2800000 bytes
HTML transferred:       220000 bytes
Requests per second:    53188.66 [#/sec] (mean)
Time per request:       1.880 [ms] (mean)
Time per request:       0.019 [ms] (mean, across all concurrent requests)
Transfer rate:          7271.89 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    0   0.1      0       1
Processing:     1    2   0.3      2       4
Waiting:        1    2   0.3      2       4
Total:          1    2   0.4      2       5

Percentage of the requests served within a certain time (ms)
  50%      2
  66%      2
  75%      2
  80%      2
  90%      2
  95%      2
  98%      3
  99%      3
 100%      5 (longest request)
```

```shell
#单进程 ab -n20000 -c20000 -k
Concurrency Level:      10000
Time taken for tests:   0.369 seconds
Complete requests:      10000
Failed requests:        0
Keep-Alive requests:    10000
Total transferred:      1400000 bytes
HTML transferred:       110000 bytes
Requests per second:    27105.27 [#/sec] (mean)
Time per request:       368.932 [ms] (mean)
Time per request:       0.037 [ms] (mean, across all concurrent requests)
Transfer rate:          3705.80 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    4  21.7      0     131
Processing:     4    7   2.4      5      14
Waiting:        4    7   2.4      5      14
Total:          4   11  21.5      5     137

Percentage of the requests served within a certain time (ms)
  50%      5
  66%     10
  75%     10
  80%     10
  90%     10
  95%     10
  98%    129
  99%    133
 100%    137 (longest request)

```
