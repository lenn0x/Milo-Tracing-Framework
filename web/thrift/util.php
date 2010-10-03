<?php
    /* Thrift Utilities */
    $GLOBALS['THRIFT_ROOT'] = dirname(dirname(__file__)) . "/thrift/lib";
    require_once $GLOBALS['THRIFT_ROOT'].'/Thrift.php';
    require_once $GLOBALS['THRIFT_ROOT'].'/protocol/TBinaryProtocol.php';
    require_once $GLOBALS['THRIFT_ROOT'].'/transport/TMemoryBuffer.php';

    function thrift_serialize($object) {
        $transport = new TMemoryBuffer();
        $oproto  = new TBinaryProtocol($transport);
        $object->write($oproto);
        return $transport->getBuffer();
    }

    function thrift_unserialize($bytes, $class) {
        $transport = new TMemoryBuffer($bytes);
        $iproto  = new TBinaryProtocol($transport);
        $class->read($iproto);
        return $class;
    }
?>