package com.milo.utils;


public class EventUtils {
    public static long createTimestamp() {
        return System.nanoTime() / 1000;
    }
}
