"""Milo TThreadServer

TODO:
    - Create generic Milo server that can be inherited from Threads, and NonBlocking Servers.
    - Make generic server allow you to pass in custom transports like Scribe
"""
from thrift.server.TServer import TThreadedServer as ThriftThreadedServer
from thrift.transport import TTransport
from milo.thrift.ttypes import EventType
from scribe import scribe
from scribe.ttypes import LogEntry
from milo.clients.thrift_client import ThriftClient
import milo.util.span as span_util
import milo.storage.thread_storage as thread_storage
import logging
import time
import threading
import socket

class TThreadedServer(ThriftThreadedServer):
    def __init__(self, *args, **kwargs):
        ThriftThreadedServer.__init__(self, *args, **kwargs)
        self.debug = False
        self.service_name = self.getServiceName()
        self.scribe = ThriftClient(scribe, "localhost", 1463)
        self.scribe_category = "Milo-Trace"
        self.rlock = threading.RLock()
        self.server_ip = socket.gethostbyname(socket.gethostname())

    def logSpans(self, trace_context):
        self.rlock.acquire()
        try:
            if not self.scribe.isOpen():
                self.scribe.open()

            data = span_util.serializeSpans(trace_context.getSpans())
            trace_context.clear()
            message = LogEntry(category=self.scribe_category, message=data)
            self.scribe.Log([message])

        except Exception, e:
            logging.error("Scribe error: " + e.message)
            self.scribe.close()

        self.rlock.release()

    def getServiceName(self):
        return self.processor.__module__.split(".")[-1].capitalize()

    def handle(self, client):
        itrans = self.inputTransportFactory.getTransport(client)
        otrans = self.outputTransportFactory.getTransport(client)
        iprot = self.inputProtocolFactory.getProtocol(itrans)
        oprot = self.outputProtocolFactory.getProtocol(otrans)

        try:
            while True:
                start_time = time.time() * 1000000
                self.processor.process(iprot, oprot)
                end_time = time.time() * 1000000

                tctx = thread_storage.getTraceContext()
                if tctx.isEnabled():
                    name = span_util.createSpanName(self.service_name, \
                                                    tctx.getRPCMethod())
                    span = span_util.createSpan(name, tctx.getTraceID(), \
                                                tctx.getSpanID(), tctx.getSpanParentID())
                    span.server_host = self.server_ip
                    span.events = [span_util.createEvent(start_time, EventType.SERVER_RECV), \
                                   span_util.createEvent(end_time, EventType.SERVER_SEND)]

                    if self.debug:
                        logging.debug(span)

                    tctx.addSpan(span)
                    self.logSpans(tctx)

        except TTransport.TTransportException, tx:
            pass
        except Exception, x:
            logging.exception(x)

        itrans.close()
        otrans.close()


class TDebugThreadedServer(TThreadedServer):
    def __init__(self, *args, **kwargs):
        TThreadedServer.__init__(self, *args)
        self.debug = True
        logger = logging.getLogger()
        logger.setLevel(logging.DEBUG)
