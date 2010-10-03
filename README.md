
# Milo Tracing Framework

Milo is designed to remove uncertainty and provide insight into how applications are performing.
It is a tracing infrastructure with goals of providing low overhead, and application-level transparency.
Milo collects data in real-time from applications using adaptive sampling, and provides a central location
to analyze performance data.

## Goals

- Low overhead
- Application-level transparency
- Scalability

## Architecture

 In a service oriented architecture, you have many clients and servers that have specific purposes. Using the Milo
 Tracing framework, you can make your Thrift Services trace aware.

 Clients can touch dozens of services, which in turn could query multiple data stores and other services.
 The Milo Tracing framework will send trace data out of band to scribe, which will handle sending the trace data
 over to Milo Collectors. The purpose of a Milo collector is to write the trace data to Cassandra and MySQL. Cassandra
 is used for storing raw trace data, and MySQL is used for being able to search across what traces have been created.

 Below is a very basic callgraph of a frontend request talking to services [A,B].

                    Frontend Request
                      /           \
                     /             \
                  Service        Service
                    A               B

 Each service will send it's specific trace data that was generated, out of band to Scribe. One thing to note,
 as each depth of the tree increases, each service is only responsible for it's trace data. When services talk to
 each other over Thrift, the only additional data that is sent across the wire is:
    - Trace ID (64-bit probabilistic identifer)
    - Span ID (64-bit probabilistic identifer)
    - Span Parent ID (64-bit probabilistic identifer)

 It's the collectors job to reconstruct the tree later in an offline process.

## Building

You need:

- java 1.6
- thrift 0.2.0

You need:

- java 1.6
- maven 2
- thrift 0.2.0
- scribe
- cassandra 0.6
- mysql 5
- php 5

Optional:

- python
- python-scribe
- python-thrift

## Running

Configuring Cassandra:
    Add this to your storage config xml for the cluster that will hold tracing data:
        <Keyspaces>
            <Keyspace Name="Milo">
                <ColumnFamily Name="Traces" CompareWith="BytesType"/>
            </Keyspace>
        </Keyspaces>

Configuring Scribe:

    At the moment Scribe is used as a transport mechanism that forwards traces to collectors.

    scribe.conf:
        <store>
        category=Milo-Trace
        type=network
        remote_host=127.0.0.1
        remote_port=2100
        max_write_interval=1
        </store>

Configuring MySQL:

    Create a database called `milo`.
    Import SQL tables from ./sql/create_tables.sql

Configuring the Milo Collector:

    Edit the defaults to fit your needs

    $ vim conf/collector.yaml

Starting the Milo Collector:

    $ mvn package
    $ bin/milo_collector

Testing out a client/server application that is Milo Trace aware:

    Start the server:

    $ python src/py/examples/milo_server.py

    Start the client:

    $ python src/py/examples/milo_client.py

Configuring the web interface:

    Edit the defaults in web/includes/config.php

    Copy the files under web/ to a folder on your webserver, you can name it `milo`.

    Enjoy!

## Community

License: Apache 2 (see included LICENSE file)
