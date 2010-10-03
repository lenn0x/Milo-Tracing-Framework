package com.milo.utils;

import java.util.Map;
import java.util.List;
import java.util.HashMap;
import java.util.Random;
import java.io.ByteArrayOutputStream;
import java.io.IOException;

import com.milo.thrift.Span;
import com.milo.thrift.Event;
import com.milo.thrift.EventType;
import org.apache.thrift.TSerializer;
import org.apache.thrift.TException;

public class SpanUtils {
    private static int RECORD_DELIMITER = 0x8badf00d;
    private static final TSerializer tserializer = new TSerializer();
    private static final ByteArrayOutputStream bufOut = new ByteArrayOutputStream();
    private static final Random rand = new Random();

    public static byte[] serializeSpan(Span span) throws TException, IOException {
        bufOut.reset();
        byte[] message = tserializer.serialize(span);

        bufOut.write(RECORD_DELIMITER);
        bufOut.write(message.length);
        bufOut.write(message);
        return bufOut.toByteArray();
    }

    public static byte[] serializeSpans(List<Span> spans) throws TException, IOException {
        bufOut.reset();
        for (Span span : spans) {
            byte[] message = tserializer.serialize(span);

            bufOut.write(ByteUtils.intToByteArray(RECORD_DELIMITER));
            bufOut.write(ByteUtils.intToByteArray(message.length));
            bufOut.write(message);
        }

        return bufOut.toByteArray();
    }

    /* Generate a probabilistic unique 64-bit integer */
    public static long generateID() {
        return rand.nextLong();
    }

    /* Generate a root span */
    public static Span createRootSpan(String name, long trace_id) {
        return createSpan(name, trace_id, generateID(), 0);
    }

    public static Span createSpan(String name, long trace_id, long span_id, long span_parent_id) {
        Span span = new Span();
        span.setName(name);
        span.setTrace_id(trace_id);
        span.setId(span_id);
        span.setParent_id(span_parent_id);
        return span;
    }

    public static Event createEvent(long timestamp, EventType event_type) {
        Event ev = new Event();
        ev.setEvent_type(event_type);
        ev.setTimestamp(timestamp);
        return ev;
    }

    public static String createSpanName(String service_name, String rpc_method) {
        return service_name + "." + rpc_method;
    }
}
