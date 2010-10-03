"""Basic Thrift server that uses the Milo Protocol"""

import sys
import os
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from thrift.transport import TSocket
from thrift.transport import TTransport
from thrift.protocol import TBinaryProtocol
from thrift.server import TServer
from helloworld import HelloWorld
from milo.protocols.thrift import TMiloProtocolFactory
from milo.servers.thrift.TThreadedServer import TDebugThreadedServer

class HelloWorldHandler:
  def ping(self, name):
    return name

handler = HelloWorldHandler()
processor = HelloWorld.Processor(handler)
transport = TSocket.TServerSocket(9090)
tfactory = TTransport.TFramedTransportFactory()
pfactory = TMiloProtocolFactory()

server = TDebugThreadedServer(processor, transport, tfactory, pfactory)

print 'Starting the server...'
server.serve()
print 'done.'