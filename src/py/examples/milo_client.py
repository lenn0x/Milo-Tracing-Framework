"""Milo Client Example"""
import sys
import os
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from scribe import scribe
from scribe.ttypes import LogEntry
from milo.clients.thrift_client import MiloThriftClient, ThriftClient
from milo.util.span import serializeSpans, createRootSpan
from helloworld import HelloWorld

service_host = "localhost"
service_port = 9090
scribe_host  = "localhost"
scribe_port  = 1463

client = MiloThriftClient(HelloWorld, service_host, service_port)

# create root span
root = createRootSpan("MiloClientExample")
# create some custom key/value pairs that can be searchable
root.annotations = dict(synapse="take over the world")
root.client_host = "1.2.3.4"

try:
    client.enableTrace(root)
    client.open()
    client.ping("Chris Goffinet")
    client.ping("Milo Hoffman")
    client.ping("Gary Winston")

    # print out spans
    spans = client.getTraceContext().getSpans()
    print spans

    """
        Example of taking the span data from MiloThriftClient
        This type of logic should be moved into your own framework on the
        client and server side.
    """
    scribe_client = ThriftClient(scribe, scribe_host, scribe_port)
    try:
        scribe_client.open()
        # Serialize spans and write them to scribe which forwards to collector
        message = LogEntry(category="Milo-Trace", message=serializeSpans(spans))
        scribe_client.Log([message])
        scribe_client.close()
    finally:
        scribe_client.close()

finally:
    client.close()
