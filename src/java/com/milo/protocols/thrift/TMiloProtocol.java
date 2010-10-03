package com.milo.protocols.thrift;

import org.apache.thrift.protocol.*;
import org.apache.thrift.transport.TTransport;
import org.apache.thrift.TException;
import com.milo.storage.ThreadStorage;
import com.milo.tracer.TraceContext;
import com.milo.utils.SpanUtils;
import com.milo.protocols.thrift.TMiloMessageType;


public class TMiloProtocol extends TBinaryProtocol {
    private TraceContext traceContext = ThreadStorage.getTraceContext();

    public TMiloProtocol(TTransport tTransport) {
        super(tTransport);
    }

    public TMiloProtocol(TTransport tTransport, boolean b, boolean b1) {
        super(tTransport, b, b1);
    }

    /**
     * Factory
     */
    public static class Factory implements TProtocolFactory {
        protected boolean strictRead_ = false;
        protected boolean strictWrite_ = true;
        protected int readLength_;

        public TProtocol getProtocol(TTransport trans) {
            TMiloProtocol proto = new TMiloProtocol(trans, strictRead_, strictWrite_);
            if (readLength_ != 0) {
                proto.setReadLength(readLength_);
            }
            return proto;
        }
    }

    public void writeMessageBegin(TMessage message) throws TException {
        byte message_type = message.type;

        if (message.type == TMessageType.CALL && traceContext.isEnabled()) {
            message_type = TMiloMessageType.CALLTRACE;
        }
        writeString(message.name);
        writeByte(message_type);
        writeI32(message.seqid);

        if (message_type == TMiloMessageType.CALLTRACE) {
            writeI64(traceContext.getTrace_id());
            /* generate new 64-bit span id */
            writeI64(SpanUtils.generateID());
            writeI64(traceContext.getSpan_parent_id());
        }
    }

    public TMessage readMessageBegin() throws TException {
        int size = readI32();
        TMessage msg;

        if (size < 0) {
            int version = size & VERSION_MASK;
            if (version != VERSION_1) {
                throw new TProtocolException(TProtocolException.BAD_VERSION, "Bad version in readMessageBegin");
            }
            msg = new TMessage(readString(), (byte) (size & 0x000000ff), readI32());
        } else {
            if (strictRead_) {
                throw new TProtocolException(TProtocolException.BAD_VERSION, "Missing version in readMessageBegin, old client?");
            }
            msg = new TMessage(readStringBody(size), readByte(), readI32());
        }

        // set trace
        if (msg.type == TMiloMessageType.CALLTRACE) {
            long trace_id = readI64();
            int span_id = readI32();
            int span_parent_id = readI32();
            traceContext.setEnabled(true);
            traceContext.setRPCMethod(msg.name);
            traceContext.setTrace_id(trace_id);
            traceContext.setSpan_id(span_id);
            traceContext.setSpan_parent_id(span_parent_id);
        } else {
            traceContext.setEnabled(false);
        }

        return msg;
    }

}
