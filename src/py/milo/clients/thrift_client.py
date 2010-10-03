import socket
import time

from thrift.transport import TTransport
from thrift.transport import TSocket
from thrift.protocol import TBinaryProtocol
from thrift import Thrift
from milo.protocols.thrift import TMiloProtocol
from milo.thrift.ttypes import EventType
import milo.storage.thread_storage as thread_storage
import milo.util.span as span_util

class ThriftClient(object):
    def __init__(self, service, host, port, service_name=None, timeout=None, transport="framed"):
        self.socket = TSocket.TSocket(host, port)
        self.service = service

        if service_name:
            self.service_name = service_name
        else:
            self.service_name = service.__name__.split(".")[-1].capitalize()

        if timeout:
            self.socket.setTimeout(timeout)

        if transport == "framed":
            self.transport = TTransport.TFramedTransport(self.socket)
        elif transport == "buffered":
            self.transport = TTransport.TBufferedTransport(self.socket)
        else:
            raise ValueError("%s transport not supported" % (transport))

        self.protocol = TBinaryProtocol.TBinaryProtocol(self.transport)
        self.client_ = self.service.Client(self.protocol)
        self.client_.transport = self.transport

    def isOpen(self):
        """Is the connection open?"""
        return self.client_.transport.isOpen()

    def open(self):
        """Connect to host"""
        self.client_.transport.open()

    def close(self):
        """Disconnect from host"""
        self.client_.transport.close()

    def __getattr__(self, attr):
        def default_method(*args, **kwargs):
            return getattr(self.client_, attr).__call__(*args, **kwargs)
        return default_method

class MiloThriftClient(ThriftClient):
    def __init__(self, *args, **kwargs):
        ThriftClient.__init__(self, *args, **kwargs)
        self.trace_context = thread_storage.getTraceContext()
        self.client_ip = socket.gethostbyname(socket.gethostname())
        self.protocol = TMiloProtocol(self.transport)
        self.client_ = self.service.Client(self.protocol)
        self.client_.transport = self.transport

    def enableTrace(self, root_span):
        self.trace_context.addSpan(root_span)
        self.trace_context.setTraceID(root_span.trace_id)
        self.trace_context.setSpanParentID(root_span.id)
        self.trace_context.setEnabled(True)

    def getTraceContext(self):
        return self.trace_context

    def logSpan(self, rpc_method, start_time, end_time):
        trace_id = self.trace_context.getTraceID()
        span_id = self.trace_context.getSpanID()
        span_parent_id = self.trace_context.getSpanParentID()
        name = span_util.createSpanName(self.service_name, rpc_method)
        span = span_util.createSpan(name=name,
                                     trace_id=trace_id,
                                     span_id=span_id,
                                     span_parent_id=span_parent_id)

        span.client_host = self.client_ip
        span.events = [span_util.createEvent(long(start_time), EventType.CLIENT_RECV), \
                       span_util.createEvent(long(end_time), EventType.CLIENT_SEND)]
        self.trace_context.addSpan(span)

    def __getattr__(self, attr):
        def default_method(*args, **kwargs):
            start_time = time.time() * 1000000
            func = getattr(self.client_, attr).__call__(*args, **kwargs)
            end_time = time.time() * 1000000

            if self.trace_context.isEnabled():
                self.logSpan(attr, start_time, end_time)

            return func
        return default_method
