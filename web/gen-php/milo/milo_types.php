<?php
/**
 * Autogenerated by Thrift
 *
 * DO NOT EDIT UNLESS YOU ARE SURE THAT YOU KNOW WHAT YOU ARE DOING
 */
include_once $GLOBALS['THRIFT_ROOT'].'/Thrift.php';


$GLOBALS['milo_E_EventType'] = array(
  'CLIENT_SEND' => 1,
  'CLIENT_RECV' => 2,
  'SERVER_SEND' => 3,
  'SERVER_RECV' => 4,
  'CUSTOM' => 5,
);

final class milo_EventType {
  const CLIENT_SEND = 1;
  const CLIENT_RECV = 2;
  const SERVER_SEND = 3;
  const SERVER_RECV = 4;
  const CUSTOM = 5;
  static public $__names = array(
    1 => 'CLIENT_SEND',
    2 => 'CLIENT_RECV',
    3 => 'SERVER_SEND',
    4 => 'SERVER_RECV',
    5 => 'CUSTOM',
  );
}

$GLOBALS['milo_E_ResultCode'] = array(
  'OK' => 0,
  'TRY_LATER' => 1,
);

final class milo_ResultCode {
  const OK = 0;
  const TRY_LATER = 1;
  static public $__names = array(
    0 => 'OK',
    1 => 'TRY_LATER',
  );
}

class milo_Event {
  static $_TSPEC;

  public $timestamp = null;
  public $event_type = null;
  public $value = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'timestamp',
          'type' => TType::I64,
          ),
        2 => array(
          'var' => 'event_type',
          'type' => TType::I32,
          ),
        3 => array(
          'var' => 'value',
          'type' => TType::STRING,
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['timestamp'])) {
        $this->timestamp = $vals['timestamp'];
      }
      if (isset($vals['event_type'])) {
        $this->event_type = $vals['event_type'];
      }
      if (isset($vals['value'])) {
        $this->value = $vals['value'];
      }
    }
  }

  public function getName() {
    return 'Event';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::I64) {
            $xfer += $input->readI64($this->timestamp);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::I32) {
            $xfer += $input->readI32($this->event_type);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->value);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('Event');
    if ($this->timestamp !== null) {
      $xfer += $output->writeFieldBegin('timestamp', TType::I64, 1);
      $xfer += $output->writeI64($this->timestamp);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->event_type !== null) {
      $xfer += $output->writeFieldBegin('event_type', TType::I32, 2);
      $xfer += $output->writeI32($this->event_type);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->value !== null) {
      $xfer += $output->writeFieldBegin('value', TType::STRING, 3);
      $xfer += $output->writeString($this->value);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class milo_Span {
  static $_TSPEC;

  public $trace_id = null;
  public $name = null;
  public $id = null;
  public $parent_id = null;
  public $client_host = null;
  public $server_host = null;
  public $events = null;
  public $annotations = null;
  public $counters = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'trace_id',
          'type' => TType::I64,
          ),
        2 => array(
          'var' => 'name',
          'type' => TType::STRING,
          ),
        3 => array(
          'var' => 'id',
          'type' => TType::I64,
          ),
        4 => array(
          'var' => 'parent_id',
          'type' => TType::I64,
          ),
        5 => array(
          'var' => 'client_host',
          'type' => TType::STRING,
          ),
        6 => array(
          'var' => 'server_host',
          'type' => TType::STRING,
          ),
        7 => array(
          'var' => 'events',
          'type' => TType::LST,
          'etype' => TType::STRUCT,
          'elem' => array(
            'type' => TType::STRUCT,
            'class' => 'milo_Event',
            ),
          ),
        8 => array(
          'var' => 'annotations',
          'type' => TType::MAP,
          'ktype' => TType::STRING,
          'vtype' => TType::STRING,
          'key' => array(
            'type' => TType::STRING,
          ),
          'val' => array(
            'type' => TType::STRING,
            ),
          ),
        9 => array(
          'var' => 'counters',
          'type' => TType::MAP,
          'ktype' => TType::STRING,
          'vtype' => TType::I64,
          'key' => array(
            'type' => TType::STRING,
          ),
          'val' => array(
            'type' => TType::I64,
            ),
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['trace_id'])) {
        $this->trace_id = $vals['trace_id'];
      }
      if (isset($vals['name'])) {
        $this->name = $vals['name'];
      }
      if (isset($vals['id'])) {
        $this->id = $vals['id'];
      }
      if (isset($vals['parent_id'])) {
        $this->parent_id = $vals['parent_id'];
      }
      if (isset($vals['client_host'])) {
        $this->client_host = $vals['client_host'];
      }
      if (isset($vals['server_host'])) {
        $this->server_host = $vals['server_host'];
      }
      if (isset($vals['events'])) {
        $this->events = $vals['events'];
      }
      if (isset($vals['annotations'])) {
        $this->annotations = $vals['annotations'];
      }
      if (isset($vals['counters'])) {
        $this->counters = $vals['counters'];
      }
    }
  }

  public function getName() {
    return 'Span';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::I64) {
            $xfer += $input->readI64($this->trace_id);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->name);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::I64) {
            $xfer += $input->readI64($this->id);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 4:
          if ($ftype == TType::I64) {
            $xfer += $input->readI64($this->parent_id);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 5:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->client_host);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 6:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->server_host);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 7:
          if ($ftype == TType::LST) {
            $this->events = array();
            $_size0 = 0;
            $_etype3 = 0;
            $xfer += $input->readListBegin($_etype3, $_size0);
            for ($_i4 = 0; $_i4 < $_size0; ++$_i4)
            {
              $elem5 = null;
              $elem5 = new milo_Event();
              $xfer += $elem5->read($input);
              $this->events []= $elem5;
            }
            $xfer += $input->readListEnd();
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 8:
          if ($ftype == TType::MAP) {
            $this->annotations = array();
            $_size6 = 0;
            $_ktype7 = 0;
            $_vtype8 = 0;
            $xfer += $input->readMapBegin($_ktype7, $_vtype8, $_size6);
            for ($_i10 = 0; $_i10 < $_size6; ++$_i10)
            {
              $key11 = '';
              $val12 = '';
              $xfer += $input->readString($key11);
              $xfer += $input->readString($val12);
              $this->annotations[$key11] = $val12;
            }
            $xfer += $input->readMapEnd();
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 9:
          if ($ftype == TType::MAP) {
            $this->counters = array();
            $_size13 = 0;
            $_ktype14 = 0;
            $_vtype15 = 0;
            $xfer += $input->readMapBegin($_ktype14, $_vtype15, $_size13);
            for ($_i17 = 0; $_i17 < $_size13; ++$_i17)
            {
              $key18 = '';
              $val19 = 0;
              $xfer += $input->readString($key18);
              $xfer += $input->readI64($val19);
              $this->counters[$key18] = $val19;
            }
            $xfer += $input->readMapEnd();
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('Span');
    if ($this->trace_id !== null) {
      $xfer += $output->writeFieldBegin('trace_id', TType::I64, 1);
      $xfer += $output->writeI64($this->trace_id);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->name !== null) {
      $xfer += $output->writeFieldBegin('name', TType::STRING, 2);
      $xfer += $output->writeString($this->name);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->id !== null) {
      $xfer += $output->writeFieldBegin('id', TType::I64, 3);
      $xfer += $output->writeI64($this->id);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->parent_id !== null) {
      $xfer += $output->writeFieldBegin('parent_id', TType::I64, 4);
      $xfer += $output->writeI64($this->parent_id);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->client_host !== null) {
      $xfer += $output->writeFieldBegin('client_host', TType::STRING, 5);
      $xfer += $output->writeString($this->client_host);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->server_host !== null) {
      $xfer += $output->writeFieldBegin('server_host', TType::STRING, 6);
      $xfer += $output->writeString($this->server_host);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->events !== null) {
      if (!is_array($this->events)) {
        throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
      }
      $xfer += $output->writeFieldBegin('events', TType::LST, 7);
      {
        $output->writeListBegin(TType::STRUCT, count($this->events));
        {
          foreach ($this->events as $iter20)
          {
            $xfer += $iter20->write($output);
          }
        }
        $output->writeListEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    if ($this->annotations !== null) {
      if (!is_array($this->annotations)) {
        throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
      }
      $xfer += $output->writeFieldBegin('annotations', TType::MAP, 8);
      {
        $output->writeMapBegin(TType::STRING, TType::STRING, count($this->annotations));
        {
          foreach ($this->annotations as $kiter21 => $viter22)
          {
            $xfer += $output->writeString($kiter21);
            $xfer += $output->writeString($viter22);
          }
        }
        $output->writeMapEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    if ($this->counters !== null) {
      if (!is_array($this->counters)) {
        throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
      }
      $xfer += $output->writeFieldBegin('counters', TType::MAP, 9);
      {
        $output->writeMapBegin(TType::STRING, TType::I64, count($this->counters));
        {
          foreach ($this->counters as $kiter23 => $viter24)
          {
            $xfer += $output->writeString($kiter23);
            $xfer += $output->writeI64($viter24);
          }
        }
        $output->writeMapEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class milo_LogEntry {
  static $_TSPEC;

  public $category = null;
  public $message = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'category',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'message',
          'type' => TType::STRING,
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['category'])) {
        $this->category = $vals['category'];
      }
      if (isset($vals['message'])) {
        $this->message = $vals['message'];
      }
    }
  }

  public function getName() {
    return 'LogEntry';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->category);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->message);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('LogEntry');
    if ($this->category !== null) {
      $xfer += $output->writeFieldBegin('category', TType::STRING, 1);
      $xfer += $output->writeString($this->category);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->message !== null) {
      $xfer += $output->writeFieldBegin('message', TType::STRING, 2);
      $xfer += $output->writeString($this->message);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

?>
