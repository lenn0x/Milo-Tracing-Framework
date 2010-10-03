"""Thrift utilities."""

from thrift.protocol import TBinaryProtocol
from thrift.transport import TTransport


def serialize(obj):
    """Serialize a Thrift object"""
    transport = TTransport.TMemoryBuffer()
    oproto = TBinaryProtocol.TBinaryProtocolAccelerated(transport)
    obj.write(oproto)
    return transport.getvalue()


def unserialize(bytes, cls):
    """Unserialize Thrift object"""
    transport = TTransport.TMemoryBuffer(bytes)
    iprot = TBinaryProtocol.TBinaryProtocolAccelerated(transport)

    instance = cls()
    instance.read(iprot)
    return instance