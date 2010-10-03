from milo.thrift.ttypes import Span, Event
from milo.util.thrift_util import serialize
from struct import pack
import uuid
import random

RECORD_DELIMITER = -1951535091 # 0x8badf00d

def serializeSpans(spans):
    data = ""
    for span in spans:
        binary = serialize(span)
        buf = pack("!i", RECORD_DELIMITER)
        buf += pack("!i", len(binary))
        buf += binary
        data += buf
    return data

def generateID():
    """Generate a probabilistic unique 64-bit integer"""
    return uuid.uuid4().int >> 65

def createRootSpan(name):
    """Generate root span node"""
    return createSpan(name, generateID(), generateID(), 0)

def createSpan(name, trace_id, span_id, span_parent_id):
    span = Span(name=name, trace_id=trace_id, parent_id=span_parent_id, \
                id=span_id)

    return span

def createSpanName(service_name, rpc_method):
    return "%s.%s" % (service_name, rpc_method)

def createEvent(timestamp, event_type):
    return Event(timestamp=timestamp, event_type=event_type)
