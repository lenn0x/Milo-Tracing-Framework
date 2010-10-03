package com.milo.storage;

import com.milo.tracer.TraceContext;


public class ThreadStorage {

     private static ThreadLocal traceContext = new ThreadLocal() {
         protected synchronized Object initialValue() {
             return new TraceContext();
         }
     };

     public static TraceContext getTraceContext() {
         return (TraceContext) traceContext.get();
     }
}
