package com.milo.utils;

public abstract class WrappedRunnable implements Runnable
{
    public final void run()
    {
        try
        {
            runMayThrow();
        }
        catch (Exception e)
        {
            throw new RuntimeException(e);
        }
    }

    abstract protected void runMayThrow() throws Exception;
}

