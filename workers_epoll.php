<?php

class Worker
{
    public $onMessage; //绑定一个消息触发的回调
    public $workernum; //子进程个数
    private $_mainSocket; //保存socket服务端资源

    //构造函数，接受监听端口等
    public function __construct($addr)
    {
        $this->addr = $addr;
    }

    //服务启动
    public function runAll()
    {
        for ($i = 0; $i < $this->workernum; $i++) {
            //创建多个worker进程，并且worker监听同一个端口
            $pid = pcntl_fork(); //创建子进程
            echo $pid.PHP_EOL;
            if ($pid < 0) {
                exit("子进程创建失败");
            } elseif ($pid === 0) {
                //子进程空间，子进程执行的逻辑，子进程复制了主进程代码逻辑，fork以后，会返回0
                //var_dump($pid);
                $this->listen();

                exit; //子进程执行完退出，因为子进程fock时复制了主进程，如果不退出还会执行for循环，从而导致子进程嵌套创建子进程
            } else {
                //大于0为父进程空间（父进程执行逻辑），pid为子进程进程id

            }
        }

    }

    //监听服务端发送的请求
    protected function listen()
    {
        $context = stream_context_create([ //创建上下文，socket、http、ftp、ssl、curl等
            'socket' => [
                'backlog' => 10000, //等待链接的个数
                'so_reuseport' => 1 //可以端口复用，允许多个进程监听同一端口
            ]
        ]);

        $this->_mainSocket = stream_socket_server($this->addr, $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $context); //创建socket，绑定端口并监听相当于socket_create socket_bind和socket_listen,子进程中设置端口监听，多个子进程监听同一个端口，会有端口占用的问题
        //使用端口复用，可以解决这个问题，系统内核进行负载均衡，php7.0以上才可以使用，避免了惊群效应（多个进程监听同一个端口，一个链接过来后，唤醒所有进程，即为惊群效应）
        //监听地址，错误号，错误信息，flag 绑定并且监听，上下文

        //异步监听
        swoole_event_add($this->_mainSocket, function ($fd) { //添加一个socket到epoll的事件监听列表中
            //参数为接收到的文件描述符
            //var_dump($fd);
            $client_socket = stream_socket_accept($fd); //获取客户端内容

            //添加一个客户端socket到epoll事件监听，当socket状态法神改变的时候执行回调
            //服务器端的socket是个监听，一般不会发生状态改变，客户端的这个socket接受数据，会有状态的改变
            //accept到了数据，就会出发client的event
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

