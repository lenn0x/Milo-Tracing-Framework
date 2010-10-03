<?php
    include_once "config.php";

    $GLOBALS['THRIFT_ROOT'] = dirname(dirname(__file__)) . "/thrift/lib";
    require_once $GLOBALS['THRIFT_ROOT'].'/Thrift.php';
    require_once $GLOBALS['THRIFT_ROOT'].'/protocol/TBinaryProtocol.php';
    require_once $GLOBALS['THRIFT_ROOT'].'/transport/TSocket.php';
    require_once $GLOBALS['THRIFT_ROOT'].'/transport/TBufferedTransport.php';
    error_reporting(0);
    $GEN_DIR = dirname(dirname(__file__)) . "/gen-php";
    require_once $GEN_DIR.'/cassandra/Cassandra.php';
    require_once $GEN_DIR.'/cassandra/cassandra_types.php';
    error_reporting(E_ALL);

    function get_client($host=null, $port=9160)
    {
        if ($host === null) {
            $host = $GLOBALS['CONFIG']['CASSANDRA']['HOST'];
        }
        if ($port === null) {
            $port = $GLOBALS['CONFIG']['CASSANDRA']['PORT'];
        }
        $socket = new TSocket($host, $port);
        $socket->setRecvTimeout(30000);
        $socket->setSendTimeout(30000);
        $transport = new TBufferedTransport($socket, 1024, 1024);
        $protocol = new TBinaryProtocol($transport);
        $client = new CassandraClient($protocol);
        $client->transport = $transport;
        return $client;
    }
?>