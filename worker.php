<?php
class Worker{
    public $onMessage; //绑定一个消息触发的回调
    private $_mainSocket; //保存socket服务端资源

    //构造函数，接受监听端口等
    public function __construct($addr){
        $this->_mainSocket = stream_socket_server($addr); //创建socket，绑定端口并监听相当于socket_create socket_bind和socket_listen

    }

    //服务启动
    public function runAll(){
        $this->listen();
    }

    //监听服务端发送的请求
    protected function listen(){
        while(true){
            //阻塞获取客户端请求
            $clientSocket = stream_socket_accept($this->_mainSocket); //从socket中读取数据，有就读取，没有就等待，得到的时文件描述符
            //var_dump((int)$clientSocket) 实际上获取到的式客户端文件资源描述符

            //从客户端的socket读取用户数据
            $message = fread($clientSocket,65535); //如果是浏览器请求，获取到的式请求头和请求体

            //如果有用户自定义的onMessage函数，就去执行
            //var_dump($this->onMessage);
            if(is_callable($this->onMessage)) {

                call_user_func($this->onMessage, $clientSocket, $this, $message);
            }
            //fwrite($clientSocket,'返回的内容');
        }
    }

    //发送消息 向那个client返回，返回的内容
    public function send($fd,$message){
        //增加http头
        $http_resonse = "HTTP/1.1 200 OK\r\n";
        $http_resonse .= "Content-Type: text/html;charset=UTF-8\r\n";
        $http_resonse .= "Connection: keep-alive\r\n";
        $http_resonse .= "Server: php socket server\r\n";
        $http_resonse .= "Content-length: ".strlen($message)."\r\n\r\n";
        $http_resonse .= $message;
        fwrite($fd,$http_resonse);
        //fclose($fd); //关闭连接，单进程的话，处理完请求直接关闭，并发处理很快，但是不适用于长连接情况，就是不进行连接的关闭
    }

}

$a = new Worker("tcp://0.0.0.0:8001");


//接收到了客户端发送的消息，回调执行
$a->onMessage = function($fd,$connect,$message){
    //业务

    //$connect->send($fd,$message);
    //返回
    $connect->send($fd,"hello world");
};

$a->runAll();