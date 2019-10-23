<?php
require_once("workers_epoll.php");
//启动监听
$worker = new Worker("tcp://0.0.0.0:8080");

//接收到了客户端发送的消息，回调执行, $message为前端发送的数据，包括头
$worker->onMessage = function($fd,$connnect,$message){
    //业务

    //$connect->send($fd,$message); //同一次请求不能send 两次
    //返回
    $connnect->send($fd,"hello world");
};

$worker->workernum = 10;
$worker->runAll(); //启动

for($i=0;$i<$worker->workernum;$i++){
    //回收子进程，不回收可能变成僵尸进程
    $pid = pcntl_wait($status,WUNTRACED); //父进程会等待子进程退出，返回回收的pid，等待或返回fork的子进程状态
    // WNOHANG    如果没有子进程退出立刻返回。
    // WUNTRACED    子进程已经退出并且其状态未报告时返回
}
