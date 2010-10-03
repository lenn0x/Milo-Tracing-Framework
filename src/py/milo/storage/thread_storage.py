from threading import local
from milo.tracer.trace_context import TraceContext

context = local()

def getTraceContext():
    if not hasattr(context, 'traceContext'):
        context.traceContext = TraceContext()
    return context.traceContext