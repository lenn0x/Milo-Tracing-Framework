package com.milo.servers.thrift;

import org.apache.thrift.TException;
import org.apache.thrift.TProcessor;
import org.apache.thrift.TProcessorFactory;
import org.apache.thrift.server.TServer;
import org.apache.thrift.protocol.TProtocol;
import org.apache.thrift.protocol.TProtocolFactory;
import org.apache.thrift.transport.TServerTransport;
import org.apache.thrift.transport.TTransport;
import org.apache.thrift.transport.TTransportException;
import org.apache.thrift.transport.TTransportFactory;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.SynchronousQueue;
import java.util.concurrent.ThreadPoolExecutor;
import java.util.concurrent.TimeUnit;
import java.net.InetAddress;

import com.milo.tracer.TraceContext;
import com.milo.storage.ThreadStorage;
import com.milo.executors.ScribeExecutor;
import com.milo.utils.SpanUtils;
import com.milo.protocols.thrift.TMiloProtocol;
import com.milo.thrift.Span;
import com.milo.thrift.EventType;
import com.milo.thrift.Event;

/**
 * Server which uses Java's built in ThreadPool management to spawn off
 * a worker pool that that uses the Milo framework.
 */
public class TThreadPoolServer extends TServer {

    private static final Logger LOGGER = LoggerFactory.getLogger(TThreadPoolServer.class.getName());

    // Executor service for handling client connections
    private ExecutorService executorService_;
    private ScribeExecutor scribeExecutorService_ = new ScribeExecutor();

    // Flag for stopping the server
    private volatile boolean stopped_;

    // Server options
    private Options options_;

    // Customizable server options
    public static class Options {
        public int minWorkerThreads = 5;
        public int maxWorkerThreads = Integer.MAX_VALUE;
        public int stopTimeoutVal = 60;
        public TimeUnit stopTimeoutUnit = TimeUnit.SECONDS;
    }

    public TThreadPoolServer(TProcessor processor,
                             TServerTransport serverTransport) {
        this(processor, serverTransport,
                new TTransportFactory(), new TTransportFactory(),
                new TMiloProtocol.Factory(), new TMiloProtocol.Factory());
    }

    public TThreadPoolServer(TProcessorFactory processorFactory,
                             TServerTransport serverTransport) {
        this(processorFactory, serverTransport,
                new TTransportFactory(), new TTransportFactory(),
                new TMiloProtocol.Factory(), new TMiloProtocol.Factory());
    }

    public TThreadPoolServer(TProcessor processor,
                             TServerTransport serverTransport,
                             TProtocolFactory protocolFactory) {
        this(processor, serverTransport,
                new TTransportFactory(), new TTransportFactory(),
                protocolFactory, protocolFactory);
    }

    public TThreadPoolServer(TProcessor processor,
                             TServerTransport serverTransport,
                             TTransportFactory transportFactory,
                             TProtocolFactory protocolFactory) {
        this(processor, serverTransport,
                transportFactory, transportFactory,
                protocolFactory, protocolFactory);
    }

    public TThreadPoolServer(TProcessorFactory processorFactory,
                             TServerTransport serverTransport,
                             TTransportFactory transportFactory,
                             TProtocolFactory protocolFactory) {
        this(processorFactory, serverTransport,
                transportFactory, transportFactory,
                protocolFactory, protocolFactory);
    }

    public TThreadPoolServer(TProcessor processor,
                             TServerTransport serverTransport,
                             TTransportFactory inputTransportFactory,
                             TTransportFactory outputTransportFactory,
                             TProtocolFactory inputProtocolFactory,
                             TProtocolFactory outputProtocolFactory) {
        this(new TProcessorFactory(processor), serverTransport,
                inputTransportFactory, outputTransportFactory,
                inputProtocolFactory, outputProtocolFactory);
    }

    public TThreadPoolServer(TProcessorFactory processorFactory,
                             TServerTransport serverTransport,
                             TTransportFactory inputTransportFactory,
                             TTransportFactory outputTransportFactory,
                             TProtocolFactory inputProtocolFactory,
                             TProtocolFactory outputProtocolFactory) {
        super(processorFactory, serverTransport,
                inputTransportFactory, outputTransportFactory,
                inputProtocolFactory, outputProtocolFactory);
        options_ = new Options();
        executorService_ = Executors.newCachedThreadPool();
    }

    public TThreadPoolServer(TProcessor processor,
                             TServerTransport serverTransport,
                             TTransportFactory inputTransportFactory,
                             TTransportFactory outputTransportFactory,
                             TProtocolFactory inputProtocolFactory,
                             TProtocolFactory outputProtocolFactory,
                             Options options) {
        this(new TProcessorFactory(processor), serverTransport,
                inputTransportFactory, outputTransportFactory,
                inputProtocolFactory, outputProtocolFactory,
                options);
    }

    public TThreadPoolServer(TProcessorFactory processorFactory,
                             TServerTransport serverTransport,
                             TTransportFactory inputTransportFactory,
                             TTransportFactory outputTransportFactory,
                             TProtocolFactory inputProtocolFactory,
                             TProtocolFactory outputProtocolFactory,
                             Options options) {
        super(processorFactory, serverTransport,
                inputTransportFactory, outputTransportFactory,
                inputProtocolFactory, outputProtocolFactory);

        executorService_ = null;

        SynchronousQueue<Runnable> executorQueue =
                new SynchronousQueue<Runnable>();

        executorService_ = new ThreadPoolExecutor(options.minWorkerThreads,
                options.maxWorkerThreads,
                60,
                TimeUnit.SECONDS,
                executorQueue);

        options_ = options;
    }

    public String getServiceName() {
        TProcessor processor = processorFactory_.getProcessor(null);
        String parentClass = processor.getClass().getCanonicalName().split("$")[0].split(".Processor")[0];
        Class<?> aClass = null;
        try {
            aClass = Class.forName(parentClass);
        } catch (ClassNotFoundException e) {
            e.printStackTrace();
        }
        return aClass.getSimpleName();
    }

    public void serve() {
        String serviceName = getServiceName();
        try {
            serverTransport_.listen();
        } catch (TTransportException ttx) {
            LOGGER.error("Error occurred during listening.", ttx);
            return;
        }

        stopped_ = false;
        LOGGER.info("Milo tracing is enabled for this application");

        while (!stopped_) {
            int failureCount = 0;
            try {
                TTransport client = serverTransport_.accept();
                WorkerProcess wp = new WorkerProcess(client, serviceName);
                executorService_.execute(wp);
            } catch (TTransportException ttx) {
                if (!stopped_) {
                    ++failureCount;
                    LOGGER.warn("Transport error occurred during acceptance of message.", ttx);
                }
            }
        }

        executorService_.shutdown();

        // Loop until awaitTermination finally does return without a interrupted
        // exception. If we don't do this, then we'll shut down prematurely. We want
        // to let the executorService clear it's task queue, closing client sockets
        // appropriately.
        long timeoutMS = options_.stopTimeoutUnit.toMillis(options_.stopTimeoutVal);
        long now = System.currentTimeMillis();
        while (timeoutMS >= 0) {
            try {
                executorService_.awaitTermination(timeoutMS, TimeUnit.MILLISECONDS);
                break;
            } catch (InterruptedException ix) {
                long newnow = System.currentTimeMillis();
                timeoutMS -= (newnow - now);
                now = newnow;
            }
        }
    }

    public void stop() {
        stopped_ = true;
        serverTransport_.interrupt();
    }

    private class WorkerProcess implements Runnable {

        /**
         * Client that this services.
         */
        private TTransport client_;
        private String serviceName_;

        /**
         * Default constructor.
         *
         * @param client Transport to process
         */
        private WorkerProcess(TTransport client, String serviceName) {
            client_ = client;
            serviceName_ = serviceName;
        }

        /**
         * Loops on processing a client forever
         */
        public void run() {
            TProcessor processor = null;
            TTransport inputTransport = null;
            TTransport outputTransport = null;
            TProtocol inputProtocol = null;
            TProtocol outputProtocol = null;
            TraceContext traceContext = ThreadStorage.getTraceContext();
            try {
                processor = processorFactory_.getProcessor(client_);
                String parentClass = processor.getClass().getCanonicalName().split("$")[0].split(".Processor")[0];
                Class<?> aClass = Class.forName(parentClass);
                String className = aClass.getSimpleName();
                inputTransport = inputTransportFactory_.getTransport(client_);
                outputTransport = outputTransportFactory_.getTransport(client_);
                inputProtocol = inputProtocolFactory_.getProtocol(inputTransport);
                outputProtocol = outputProtocolFactory_.getProtocol(outputTransport);
                String server_ip = InetAddress.getLocalHost().getHostAddress();

                // we check stopped_ first to make sure we're not supposed to be shutting
                // down. this is necessary for graceful shutdown.
                while (!stopped_) {
                    long start_time = System.nanoTime() / 1000;
                    processor.process(inputProtocol, outputProtocol);
                    long end_time = System.nanoTime() / 1000;

                    if (traceContext.isEnabled()) {
                        String name = SpanUtils.createSpanName(serviceName_, traceContext.getRPCMethod());
                        Span span = SpanUtils.createSpan(name, traceContext.getTrace_id(), traceContext.getSpan_id(), traceContext.getSpan_parent_id());
                        span.setServer_host(server_ip);
                        span.addToEvents(SpanUtils.createEvent(start_time, EventType.SERVER_RECV));
                        span.addToEvents(SpanUtils.createEvent(end_time, EventType.SERVER_SEND));
                        /* Iterate over any custom events */
                        for (Event event : traceContext.getEvents())
                            span.addToEvents(event);

                        scribeExecutorService_.add(traceContext.getSpans());
                        traceContext.clear();
                    }
                }
            } catch (TTransportException ttx) {
                // Assume the client died and continue silently
            } catch (TException tx) {
                LOGGER.error("Thrift error occurred during processing of message.", tx);
            } catch (Exception x) {
                LOGGER.error("Error occurred during processing of message.", x);
            }

            if (inputTransport != null) {
                inputTransport.close();
            }

            if (outputTransport != null) {
                outputTransport.close();
            }
        }
    }
}
