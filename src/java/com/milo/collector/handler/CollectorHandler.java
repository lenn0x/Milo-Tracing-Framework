package com.milo.collector.handler;

import com.milo.executors.CollectorExecutor;
import com.milo.thrift.ResultCode;
import com.milo.thrift.LogEntry;
import com.milo.thrift.MiloCollector;
import com.milo.config.ConfigurationException;

import java.util.List;
import java.io.IOException;

import org.apache.thrift.TException;

import org.apache.cassandra.thrift.*;
import org.apache.log4j.Logger;

public class CollectorHandler implements MiloCollector.Iface {
    private static Logger logger = Logger.getLogger(CollectorHandler.class);

    private Cassandra.Client client;
    // create ExecutorService to manage threads
    CollectorExecutor collectorExecutor;

    public CollectorHandler() throws IOException, ConfigurationException {
        collectorExecutor = new CollectorExecutor();
    }

    public ResultCode Log(List<LogEntry> messages) throws TException {
        collectorExecutor.add(messages);
        return ResultCode.OK;
    }

}
