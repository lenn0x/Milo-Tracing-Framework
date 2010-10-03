package com.milo.executors;

import java.util.List;
import java.util.ArrayList;
import java.util.concurrent.BlockingQueue;
import java.util.concurrent.LinkedBlockingQueue;
import java.io.IOException;
import java.net.Socket;
import org.apache.log4j.Logger;


import com.milo.thrift.LogEntry;
import com.milo.thrift.Span;
import com.milo.thrift.MiloCollector;
import com.milo.thrift.MiloCollector.Client;
import com.milo.utils.WrappedRunnable;
import com.milo.utils.SpanUtils;
import org.apache.thrift.TException;
import org.apache.thrift.protocol.TBinaryProtocol;
import org.apache.thrift.transport.TTransport;
import org.apache.thrift.transport.TTransportException;
import org.apache.thrift.transport.TSocket;
import org.apache.thrift.transport.TFramedTransport;

public class ScribeExecutor {
    private static Logger logger = Logger.getLogger(ScribeExecutor.class);
    private final BlockingQueue<List<Span>> queue;

    private TTransport transport;
    private MiloCollector.Client client;
    private final String SCRIBE_HOST = "localhost";
    private final int SCRIBE_PORT = 1463;
    private final String SCRIBE_CATEGORY = "Milo-Trace";
    private static final long RetryInterval = 500L;
    private static final long MaxInterval = 10000L;

    public ScribeExecutor()
    {
        this(1024 * Runtime.getRuntime().availableProcessors());
    }

    public ScribeExecutor(int queueSize)
    {
        queue = new LinkedBlockingQueue<List<Span>>(queueSize);

        Runnable runnable = new WrappedRunnable()
        {
            public void runMayThrow() throws Exception
            {
                connect();
                while (true)
                {
                    int retries = 0;
                    List<Span> spans = queue.take();

                    while (true)
                    {
                        if (transport != null && client != null)
                        {
                            try {
                                process(spans);
                                break;
                            }
                            catch (TTransportException e)
                            {
                                logger.warn("TTransportException to scribe (" + SCRIBE_HOST + ":" + SCRIBE_PORT + "): " + e.getMessage());
                            }
                            catch (TException e)
                            {
                                logger.warn("TException to scribe (" + SCRIBE_HOST + ":" + SCRIBE_PORT + "): " + e.getMessage());
                            }
                        }
                        ++retries;
                        retryWait(retries);
                        connect();
                    }
                }
            }
        };
        new Thread(runnable, "MiloScribeExecutorService").start();

    }

    public void process(List<Span> spans) throws TException, IOException
    {

        List<LogEntry> logEntries = new ArrayList<LogEntry>();

        LogEntry logEntry = new LogEntry();
        logEntry.category = SCRIBE_CATEGORY;
        logEntry.setCategory(SCRIBE_CATEGORY);
        logEntry.setMessage(SpanUtils.serializeSpans(spans));
        logEntries.add(logEntry);

        client.Log(logEntries);
    }

    private void retryWait(int factor)
    {
        try
        {
            long sleepFor = factor * RetryInterval;
            if (sleepFor > MaxInterval)
                sleepFor = MaxInterval;
            Thread.sleep(sleepFor);
        }
        catch (InterruptedException ignore)
        {
            // ignore, just return and fall back to connection retry
        }
    }

    private void connect()
    {
        try
        {
            if (transport != null)
                transport.close();
        }
        catch (Exception ignore)
        {
            // pass
        }

        TSocket sock = null;
        try {
            sock = new TSocket(new Socket(SCRIBE_HOST, SCRIBE_PORT));
            transport = new TFramedTransport(sock);
            TBinaryProtocol protocol = new TBinaryProtocol(transport, false, false);
            client = new Client(protocol, protocol);
            logger.info("Milo tracing is now connected to scribe <" + SCRIBE_HOST + ":" + SCRIBE_PORT + " category " + SCRIBE_CATEGORY + ">");
        } catch (TTransportException e) {
            logger.error("Milo tracing can't connect to scribe <" + SCRIBE_HOST + ":" + SCRIBE_PORT + " category " + SCRIBE_CATEGORY + ">");
        } catch (IOException e) {
            logger.error("Milo tracing can't connect to scribe <" + SCRIBE_HOST + ":" + SCRIBE_PORT + " category " + SCRIBE_CATEGORY + ">");
        }
    }


    public void add(List<Span> spans)
    {
        try
        {
            queue.put(spans);
        }
        catch (InterruptedException e)
        {
            throw new RuntimeException(e);
        }
    }
}
