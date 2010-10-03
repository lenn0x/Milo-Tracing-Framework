from milo.util.span import generateID

class TraceContext(object):
    def __init__(self):
        self.spans = []
        self.trace_id = None
        self.enabled = False
        self.span_id = 0
        self.span_parent_id = 0
        self.rpc_method = None

    def getRPCMethod(self):
        return self.rpc_method

    def setRPCMethod(self, method):
        self.rpc_method = method

    def nextSpanID(self):
        self.span_id = generateID()
        return self.span_id

    def setSpanID(self, id_):
        self.span_id = id_

    def setSpanParentID(self, id_):
        self.span_parent_id = id_

    def getSpanID(self):
        return self.span_id

    def getSpanParentID(self):
        return self.span_parent_id

    def setEnabled(self, enabled):
        self.enabled = enabled

    def isEnabled(self):
        return self.enabled

    def getTraceID(self):
        return self.trace_id

    def setTraceID(self, trace_id):
        self.trace_id = trace_id

    def addSpan(self, span):
        self.spans.append(span)
        return span

    def getSpans(self):
        return self.spans

    def clear(self):
        self.spans = []
        self.events = []

    def __repr__(self):
        return str([span for span in self.spans])

