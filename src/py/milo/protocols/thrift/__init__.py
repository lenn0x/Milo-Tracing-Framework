from thrift.protocol.TProtocol import *
from thrift.protocol.TBinaryProtocol import TBinaryProtocol
from thrift import Thrift
from struct import pack, unpack
from milo.thrift.ttypes import Span
from milo.storage.thread_storage import getTraceContext
import milo.util.span as span_util

class TMessageType:
    CALLTRACE = 5

class TMiloProtocol(TBinaryProtocol):

  """Milo protocol. This is TBinaryProtocol + Tracing capability."""

  VERSION_1 = -2147418112

  def __init__(self, trans):
    TBinaryProtocol.__init__(self, trans)
    self.strictRead = False
    self.strictWrite = True
    self.trace_context = getTraceContext()

  def writeMessageBegin(self, name, type, seqid):
      if type == Thrift.TMessageType.CALL and self.trace_context.isEnabled():
          type = TMessageType.CALLTRACE

      self.writeI32(self.VERSION_1 | type)
      self.writeString(name)
      self.writeI32(seqid)

      if type == TMessageType.CALLTRACE:
          trace_id = self.trace_context.getTraceID()
          span_id = self.trace_context.nextSpanID()
          span_parent_id = self.trace_context.getSpanParentID()

          self.writeI64(trace_id)
          self.writeI64(span_id)
          self.writeI64(span_parent_id)

  def readMessageBegin(self):
    sz = self.readI32()
    trace_id = None
    span_id = None
    span_parent_id = None
    span = None

    if sz < 0:
      version = sz & TBinaryProtocol.VERSION_MASK
      if version != self.VERSION_1:
        raise TProtocolException(type=TProtocolException.BAD_VERSION, message='Bad version in readMessageBegin: %d' % (sz))
      type = sz & TBinaryProtocol.TYPE_MASK
      name = self.readString()
      seqid = self.readI32()
    else:
      if self.strictRead:
        raise TProtocolException(type=TProtocolException.BAD_VERSION, message='No protocol version header')
      name = self.trans.readAll(sz)
      type = self.readByte()
      seqid = self.readI32()

    if type == TMessageType.CALLTRACE:
        trace_id = self.readI64()
        span_id = self.readI64()
        span_parent_id = self.readI64()
        span = Span(trace_id=trace_id, id=span_id, parent_id=span_parent_id)
        self.trace_context.setEnabled(True)
        self.trace_context.setSpanID(span_id)
        self.trace_context.setSpanParentID(span_parent_id)
        self.trace_context.setRPCMethod(name)
        self.trace_context.setTraceID(trace_id)
    elif type == Thrift.TMessageType.CALL:
        self.trace_context.setEnabled(False)

    return (name, type, seqid)

class TMiloProtocolAccelerated(TMiloProtocol):
  pass

class TMiloProtocolFactory:
  def __init__(self, strictRead=False, strictWrite=True):
    self.strictRead = strictRead
    self.strictWrite = strictWrite

  def getProtocol(self, trans):
    prot = TMiloProtocol(trans)
    return prot
