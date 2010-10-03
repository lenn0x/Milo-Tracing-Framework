package com.milo.config;

public class Config {
    public int collector_port = 2100;
    public int collector_min_workers = 64;

    /* MySQL and Cassandra settings */
    public Cassandra cassandra;
    public MySQL mysql;
    public Scribe scribe;
}
