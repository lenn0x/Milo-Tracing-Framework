package com.milo.tracer;

import com.milo.storage.ThreadStorage;
import com.milo.thrift.Event;
import com.milo.thrift.EventType;
import com.milo.utils.EventUtils;

public class Tracer {
    static TraceContext traceContext = null;

    public static TraceContext getCurrentTracer() {
        if (traceContext == null) {
            traceContext = ThreadStorage.getTraceContext();
        }
        return traceContext;
    }

    public void record(String value) {
        Event event = new Event();
        event.setTimestamp(EventUtils.createTimestamp());
        event.setEvent_type(EventType.CUSTOM);
        event.setValue(value);
        traceContext.addEvent(event);
    }
}
