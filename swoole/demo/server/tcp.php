<?php
/**
 * Created by PhpStorm.
 * User: Mikou.hu
 * Date: 2019/5/20
 */
//
//$serv = new swoole_server('127.0.0.1',9501);
//
//$serv->set([
//    'worker_num' => 6,
//    'max_request' =>10000
//]);
//
//$serv->on('connect',function ($serv,$fd,$reactor_id){
//    echo "Client: {$reactor_id} - {$fd}-Connect.\n";
//});
//
////监听数据接收事件
//$serv->on('receive', function ($serv, $fd, $reactor_id, $data) {
//    $serv->send($fd, "Server: {$reactor_id} - {$fd}".$data);
//});
//
////监听连接关闭事件
//$serv->on('close', function ($serv, $fd) {
//    echo "Client: Close.\n";
//});

//启动服务器
//$serv->start();

class TcpServer
{
    public $server;
    public function __construct()
    {
        $this->server = new Swoole\Server('127.0.0.1',9501);

        $this->server->set(
            [
                'worker_num' => 6,
                'max_request' =>10000,
                'task_worker_num'=>4
            ]
        );

        $this->server->on('Start', array($this, 'onStart'));
        $this->server->on('Connect', array($this, 'onConnect'));
        $this->server->on('Receive', array($this, 'onReceive'));
        $this->server->on('Task', array($this, 'onTask'));
        $this->server->on('Finish', array($this, 'onFinish'));

        $this->server->on('Close', array($this, 'onClose'));
        $this->server->start();

    }


    public function onStart($server)
    {
//        var_dump($server);
    }


    public function onConnect($server,$fd,$reactor_id)
    {
        echo "Client: {$reactor_id} - {$fd}-Connect.\n";
    }
    public function onReceive($server,$fd,$reactor_id,$data)
    {
        echo '收到客户端的信息 ----'.$reactor_id.'-----'.$data.PHP_EOL;
//        sleep(1);
//        $task_id = $server->task($data);
//        swoole_timer_after(2000,function () use ($server,$fd,$data){
            $server->send($fd, "我收到了你的消息 -- {$data}");
//
//        });

    }

    public function onTask($serv, $task_id, $from_id, $data)
    {
        echo "执行[id=$task_id]的任务".PHP_EOL;
        //返回任务执行的结果
        $serv->finish("$data -> OK");
    }

    public function onFinish($serv, $task_id, $data)
    {
            echo $task_id.'的任务over'.PHP_EOL;
    }

    public function onClose($server,$fd)
    {    echo "Client: Close.\n";
    }

}

$c = new TcpServer();
