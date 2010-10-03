<?php
/**
 * Milo_ThreadStorage
 *
 * @package Milo
 * @author Chris Goffinet <cg@chrisgoffinet.com>
 * @filesource
 */

require_once "../tracer/TraceContext.php";

/**
 * Milo_ThreadStorage
 *
 * Basic thread storage for containing traced sessions
 *
 * @package Milo
 * @author Chris Goffinet <cg@chrisgoffinet.com>
 */
class Milo_ThreadStorage
{
    /**
     * Used to storage local session data
     *
     * @param local
     */
    protected static $local = null;

    /**
     * Static accessor for a thread local storage instance
     *
     * @return array
     */
    static public function getLocalContext()
    {
        if(self::$local === null) {
            self::$local = new stdclass();
        }
        return self::$local;
    }

    /**
     * Static accessor for obtaining a trace context instance
     *
     * @return Milo_TraceContext
     */
    static public function getTraceContext()
    {
        $local = self::getLocalContext();
        if(!isset($local->trace_context)) {
          $local->trace_context = new Milo_TraceContext();
        }
        return $local->trace_context;
    }
}

?>
