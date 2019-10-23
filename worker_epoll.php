<?php

class Worker
{
    public $onMessage; //绑定一个消息触发的回调
    private $_mainSocket; //保存socket服务端资源

//构造函数，接受监听端口等
    public function __construct($addr)
    {
        $this->_mainSocket = stream_socket_server($addr); //创建socket，绑定端口并监听相当于socket_create socket_bind和socket_listen

    }

//服务启动
    public function runAll()
    {
        $this->listen();
    }

//监听服务端发送的请求
    protected function listen()
    {
//异步监听
        swoole_event_add($this->_mainSocket, function ($fd) { //添加一个socket到epoll的事件监听列表中
//参数为接收到的文件描述符
//var_dump($fd);
            $client_socket = stream_socket_accept($fd); //获取客户端内容

//添加一个客户端socket到epoll事件监听，当socket状态法神改变的时候执行回调
//服务器端的socket是个监听，一般不会发生状态改变，客户端的这个socket接受数据，会有状态的改变
            swoole_event_add($client_socket, function ($fd) {
                if (feof($fd) || !is_resource($fd)) {
//如果到了末尾或者不是资源类型，说明客户端已经关闭，就不进行后续处理
                    swoole_event_del($fd); //删除事件监听
                    fclose($fd);
                    return false;
//或者触发onclose事件，

                }
                $message = fread($fd, 65535);

                if (is_callable($this->onMessage)) {

                    call_user_func($this->onMessage, $fd, $this, $message);
                }

            });
});
    }

//发送消息 向那个client返回，返回的内容
    public function send($fd, $message)
    {
//增加http头
        $http_resonse = "HTTP/1.1 200 OK\r\n";
        $http_resonse .= "Content-Type: text/html;charset=UTF-8\r\n";
        $http_resonse .= "Connection: keep-alive\r\n";
        $http_resonse .= "Server: php socket server\r\n";
        $http_resonse .= "Content-length: " . strlen($message) . "\r\n\r\n";
        $http_resonse .= $message;
        fwrite($fd, $http_resonse);
//fclose($fd); //关闭连接，单进程的话，处理完请求直接关闭，并发处理很快，但是不适用于长连接情况，就是不进行连接的关闭
    }

}

//启动监听
$worker = new Worker("tcp://0.0.0.0:8080");

//接收到了客户端发送的消息，回调执行, $message为前端发送的数据，包括头
$worker->onMessage = function($fd,$connnect,$message){
    //业务

    //$connect->send($fd,$message); //同一次请求不能send 两次
    //返回
    $connnect->send($fd,"hello world");
};


$worker->runAll(); //启动
