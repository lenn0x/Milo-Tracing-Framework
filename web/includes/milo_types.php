<?php
    $GLOBALS['THRIFT_ROOT'] = dirname(dirname(__file__)) . "/thrift/lib";
    require_once $GLOBALS['THRIFT_ROOT'].'/Thrift.php';
    require_once $GLOBALS['THRIFT_ROOT'].'/protocol/TBinaryProtocol.php';
    require_once $GLOBALS['THRIFT_ROOT'].'/transport/TMemoryBuffer.php';

    error_reporting(0);
    $GEN_DIR = dirname(dirname(__file__)) . "/gen-php";
    require_once $GEN_DIR.'/milo/milo_types.php';
    error_reporting(E_ALL);

?>