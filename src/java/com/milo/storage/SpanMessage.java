package com.milo.storage;

import com.milo.thrift.Span;
import com.milo.utils.ByteUtils;
import org.apache.thrift.TDeserializer;
import org.apache.thrift.TException;

import java.io.ByteArrayInputStream;
import java.util.List;
import java.util.Map;
import java.util.HashMap;


public class SpanMessage {
    private Map<Span, byte[]> spans;
    private final TDeserializer tdeserializer;

    public SpanMessage(TDeserializer tdeserializer) {
        this.tdeserializer = tdeserializer;
        this.spans = new HashMap<Span, byte[]>();
    }
    public Map<Span, byte[]> unserialize(byte[] buffer) {

        int buf = 0;
        byte[] b = new byte[4];
        ByteArrayInputStream bufIn = new ByteArrayInputStream(buffer);

        while(buf < buffer.length) {
            Span span = new Span();
            bufIn.skip(4); // skip 4 byte header
            bufIn.read(b, 0, 4); // read span serialized length

            int length = ByteUtils.byteArrayToInt(b);
            byte[] data = new byte[length];

            try {
                // read the serialized span data
                bufIn.read(data, 0, length);
                tdeserializer.deserialize(span, data);
                this.spans.put(span, data);
            } catch (TException e) {
                e.printStackTrace();
            }
            buf += 8 + length;

        }
        return this.spans;
    }
}
