<?php

include_once $GLOBALS['THRIFT_ROOT'].'/transport/TBufferedTransport.php';
include_once $GLOBALS['THRIFT_ROOT'].'/Thrift.php';
class TMilo_MessageType {
    const CALLTRACE = 5;
}

class TMiloProtocol extends TBinaryProtocolAccelerated {

  const VERSION_MASK = 0xffff0000;
  const VERSION_1 = 0x80010000;

  protected $strictRead_ = false;
  protected $strictWrite_ = true;

  public function __construct($trans, $trace_id=null) {
    parent::__construct($trans);
    $this->strictRead_ = false;
    $this->strictWrite_ = true;
    $this->trace_id = $trace_id;
  }

  public function writeMessageBegin($name, $type, $seqid) {
      if ($type == TMessageType::CALL && $this->trace_id !== null) {
        $type = TMilo_MessageType::CALLTRACE;
      }
      $version = self::VERSION_1 | $type;
      $msg = $this->writeI32($version) + $this->writeString($name) + $this->writeI32($seqid);

      if ($type == TMilo_MessageType::CALLTRACE) {
        list($span_id, $span_parent_id) = Milo_Util::incrementSpanID();
        $msg += $this->writeI64($this->trace_id) +
                $this->writeI32($span_id) +
                $this->writeI32($span_parent_id);
      }
      return $msg;
  }
}

/**
 * Accelerated milo binary protocol: used in conjunction with the thrift_protocol
 * extension for faster deserialization
 */
class TMiloProtocolAccelerated extends TMiloProtocol {
  public function __construct($trans, $strictRead=false, $strictWrite=true) {
    // If the transport doesn't implement putBack, wrap it in a
    // TBufferedTransport (which does)
    if (!method_exists($trans, 'putBack')) {
      $trans = new TBufferedTransport($trans);
    }
    parent::__construct($trans, $strictRead, $strictWrite);
  }
  public function isStrictRead() {
    return $this->strictRead_;
  }
  public function isStrictWrite() {
    return $this->strictWrite_;
  }
}

?>
