<?php
/**
 * Milo_Util
 *
 * @package Milo
 * @author Chris Goffinet <cg@chrisgoffinet.com>
 */

require_once "../storage/ThreadStorage.php";
require_once $GLOBALS['THRIFT_ROOT'] . '/packages/milo/milo_types.php';
require_once $GLOBALS['THRIFT_ROOT'] . '/transport/TMemoryBuffer.php';
require_once $GLOBALS['THRIFT_ROOT'] . '/protocol/TBinaryProtocol.php';

/**
 * A class that provides various utility functions for use with Milo
 *
 * @author Chris Goffinet <cg@chrisgoffinet.com>
 */
class Milo_Util
{
    /**
     * The trace record delimiter
     *
     * @var integer
     */
    const RECORD_DELIMITER = 0x8badf00d;

    /**
     * Return a unique 64-bit integer used for tracing.
     *
     * @return integer
     */
    static public function generateID()
    {
        return mt_rand(1, 9223372036854775807);
    }

    /**
     * Create a root span
     *
     * @return T_Base_Span
     */
    static public function createRootSpan($name)
    {
        return self::createSpan($name, self::generateID(), self::generateID(), 0);
    }

    /**
     * Create a new span that will be used for trace context
     *
     * @return T_Base_Span
     */
    static public function createSpan($name, $trace_id, $span_id, $span_parent_id)
    {
        $vals = array("trace_id" => $trace_id, "id" => $span_id,
                       "parent_id" => $span_parent_id, "name" => $name);

        $span = new T_Base_Span($vals);
        return $span;
    }

    /**
     * Serialize span into trace record
     *
     * @param T_Base_Span
     * @return binary
     */
    static public function serialize_span($span)
    {
        $data = self::thrift_serialize($span);
        $length = strlen($data);
        return pack('N', self::RECORD_DELIMITER) . pack('N', $length) . $data;
    }
    /**
     * Create a span name
     *
     * @param $service_name
     * @param $rpc_method
     * @return string
     */

    static public function createSpanName($service_name, $rpc_method)
    {
        return "$service_name.$rpc_method";
    }

    /**
     * Create an event
     *
     * @param $timestamp
     * @param $event_type
     * @return milo_Event
     */

    static public function createEvent($timestamp, $event_type)
    {
        $vals = array("timestamp" => $timestamp, "event_type" => $event_type);
        return new milo_Event($vals);
    }


    /**
     * Serialize thrift structure
     *
     * @param object
     * @return binary
     */

    static public function thrift_serialize($object) {
        $transport = new TMemoryBuffer();
        $oproto  = new TBinaryProtocol($transport);
        $object->write($oproto);
        return $transport->getBuffer();
    }

    /**
     * Unserialize thrift structure
     *
     * @param integer
     * @param object
     * @return object
     */

    static public function thrift_unserialize($bytes, $class) {
        $transport = new TMemoryBuffer($bytes);
        $iproto  = new TBinaryProtocol($transport);
        $class->read($iproto);
        return $class;
    }
}

?>
