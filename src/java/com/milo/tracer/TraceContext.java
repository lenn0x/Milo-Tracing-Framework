package com.milo.tracer;

import java.util.List;
import java.util.ArrayList;
import java.util.Collections;

import com.milo.thrift.Span;
import com.milo.thrift.Event;


public class TraceContext {
    private long trace_id;
    private int span_id;
    private int span_parent_id;
    private String rpc_method;
    private boolean enabled = false;
    private List<Span> spans = new ArrayList<Span>();
    private List<Event> events = new ArrayList<Event>();

    public String getRPCMethod() {
        return rpc_method;
    }

    public void setRPCMethod(String name) {
        this.rpc_method = name;
    }

    public boolean isEnabled() {
        return enabled;
    }

    public void setEnabled(boolean enabled) {
        this.enabled = enabled;
    }

    public List<Span> getSpans() {
        return new ArrayList(spans);
    }

    public void clear() {
        spans.clear();
        events.clear();
    }

    public boolean addSpan(Span span) {
        return spans.add(span);
    }

    public long getTrace_id() {
        return trace_id;
    }

    public void setTrace_id(long trace_id) {
        this.trace_id = trace_id;
    }

    public int getSpan_id() {
        return span_id;
    }

    public void setSpan_id(int span_id) {
        this.span_id = span_id;
    }

    public int getSpan_parent_id() {
        return span_parent_id;
    }

    public void setSpan_parent_id(int span_parent_id) {
        this.span_parent_id = span_parent_id;
    }

    public boolean addEvent(Event event) {
        return events.add(event);
    }

    public List<Event> getEvents() {
        return new ArrayList(events);
    }
}
