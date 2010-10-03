package com.milo.executors;

import com.milo.thrift.LogEntry;
import com.milo.thrift.Span;
import com.milo.thrift.Event;
import com.milo.thrift.EventType;
import com.milo.utils.WrappedRunnable;

import java.util.*;
import java.util.concurrent.LinkedBlockingQueue;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.io.IOException;

import org.apache.thrift.TDeserializer;
import org.apache.thrift.TException;
import org.apache.thrift.protocol.TProtocol;
import org.apache.thrift.protocol.TBinaryProtocol;
import org.apache.thrift.transport.TTransport;
import org.apache.thrift.transport.TTransportException;
import org.apache.thrift.transport.TSocket;
import org.apache.cassandra.thrift.*;
import org.apache.log4j.Logger;
import com.milo.storage.SpanMessage;
import com.milo.config.Config;
import com.milo.config.CollectorConfiguration;
import com.milo.config.ConfigurationException;
import com.mysql.jdbc.Connection;
import com.mysql.jdbc.PreparedStatement;

public class CollectorExecutor {
    private static Logger logger = Logger.getLogger(CollectorExecutor.class);
    private final LinkedBlockingQueue<List<LogEntry>> queue;
    private final TDeserializer tdeserializer = new TDeserializer();
    private final int DEFAULT_SLEEP = 5000;
    protected volatile long completedTaskCount = 0;
    private Config config = CollectorConfiguration.factory();
    private TTransport transport;
    private Cassandra.Client client;
    private Connection mysql_connection;

    public CollectorExecutor() throws IOException, ConfigurationException {
        queue = new LinkedBlockingQueue<List<LogEntry>>();
        Runnable runnable = new WrappedRunnable() {
            public void runMayThrow() throws Exception {
                manageConnections();
                while (true) {
                    process(queue.take());
                    completedTaskCount++;
                }
            }
        };
        new Thread(runnable, "MiloCollectorService").start();

    }

    public void connectMySQL() throws Exception {
        if (mysql_connection == null) {
            try {
                String url = "jdbc:mysql://" + config.mysql.hostname + ":" + config.mysql.port + "/" + config.mysql.database;
                Class.forName("com.mysql.jdbc.Driver").newInstance();
                mysql_connection = (Connection) DriverManager.getConnection(url, config.mysql.username, config.mysql.password);
                logger.info("Connected to MySQL Server - " + config.mysql.hostname + ":" + config.mysql.port);
            } catch (Exception ex) {
                logger.error("Can't connect to MySQL Server - " + config.mysql.hostname + ":" + config.mysql.port);
                throw ex;
            }
        }
    }

    public void connectCassandra() throws Exception {
        if (transport == null || transport.isOpen() == false) {
            try {
                transport = new TSocket(config.cassandra.hostname, config.cassandra.port);
                TProtocol protocol = new TBinaryProtocol(transport);
                client = new Cassandra.Client(protocol);
                transport.open();
                logger.info("Connected to Cassandra Server - " + config.cassandra.hostname);
            } catch (TTransportException ex) {
                transport.close();
                logger.error("Can't connect to Cassandra");
                throw ex;
            }
        }
    }

    private void insertIntoTraceTable(Long trace_id) {
        try {
            String sql = "REPLACE INTO traces SET trace_id = ? ;";
            PreparedStatement statement = (PreparedStatement) mysql_connection.prepareStatement(sql);
            statement.setLong(1, trace_id);
            statement.executeUpdate();
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    private void insertIntoAnnotationsTable(Span span) {

        for (Map.Entry<String, String> annotation : span.annotations.entrySet()) {
            try {
                String sql = "REPLACE INTO annotations SET " +
                        "               trace_id = ?, span_id = ?, annotation_name = ?, annotation_value = ?, name = ?;";
                PreparedStatement statement = (PreparedStatement) mysql_connection.prepareStatement(sql);
                statement.setLong(1, span.trace_id);
                statement.setLong(2, span.id);
                statement.setString(3, annotation.getKey());
                statement.setString(4, annotation.getValue());
                statement.setString(5, span.name);
                statement.executeUpdate();
            } catch (SQLException e) {
                e.printStackTrace();
            }
        }

    }

    public void manageConnections() throws Exception {
        while (true) {
            try {
                connectCassandra();
                connectMySQL();
                break;

            } catch (Exception ex) {
                Thread.sleep(DEFAULT_SLEEP); // sleep for 5 second
            }
        }
    }

    public void process(List<LogEntry> messages) throws Exception {
        manageConnections();
        long timestamp = System.currentTimeMillis();

        for (LogEntry message : messages) {
            SpanMessage spanmsg = new SpanMessage(this.tdeserializer);
            Map<Span, byte[]> spans = spanmsg.unserialize(message.message);

            for (Map.Entry<Span, byte[]> spanSet : spans.entrySet()) {
                String span_type = "unknown";
                Span span = spanSet.getKey();

                if (span.isSetEvents()) {
                    for (Event event : span.events) {
                        if (event.event_type == EventType.CLIENT_RECV || event.event_type == EventType.CLIENT_SEND) {
                            span_type = "client";
                            break;
                        } else if (event.event_type == EventType.SERVER_RECV || event.event_type == EventType.SERVER_SEND) {
                            span_type = "server";
                            break;
                        }
                    }
                }
                String key = new Long(span.trace_id).toString();
                String column_name = new Long(span.id) + "-" + new Long(span.parent_id) + "-" + span_type;
                Column column = new Column();
                column.setName(column_name.getBytes());
                column.setValue(spanSet.getValue());
                column.setTimestamp(timestamp);

                ColumnOrSuperColumn cos = new ColumnOrSuperColumn();
                cos.setColumn(column);

                Mutation mutation = new Mutation();
                mutation.setColumn_or_supercolumn(cos);

                Map<String, List<Mutation>> rows = new HashMap<String, List<Mutation>>();
                rows.put(config.cassandra.column_family, Arrays.asList(mutation));

                Map<String, Map<String, List<Mutation>>> mutation_map = new HashMap<String, Map<String, List<Mutation>>>();
                mutation_map.put(key, rows);

                insertIntoTraceTable(span.trace_id);

                if (span.isSetAnnotations()) {
                    insertIntoAnnotationsTable(span);
                }

                try {
                    logger.info("Processing trace_id " + span.trace_id + " of span id " + span.id);
                    client.batch_mutate(config.cassandra.keyspace, mutation_map, ConsistencyLevel.ONE);

                } catch (InvalidRequestException e) {
                    e.printStackTrace();
                } catch (UnavailableException e) {
                    e.printStackTrace();
                } catch (TimedOutException e) {
                    e.printStackTrace();
                } catch (TException e) {
                    e.printStackTrace();
                }

            }
        }
    }

    public void add(List<LogEntry> messages) {
        try {
            queue.put(messages);
        }
        catch (InterruptedException e) {
            throw new RuntimeException(e);
        }
    }
}
