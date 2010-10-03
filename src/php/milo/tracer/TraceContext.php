<?php
/**
 * Milo_TraceContext
 *
 * @package Milo
 * @author Chris Goffinet <cg@chrisgoffinet.com>
 * @filesource
 */

require_once "../util/Util.php";

/**
 * Milo_TraceContext
 *
 * A trace context holds the trace ids and all spans associated with a trace.
 *
 * @package Milo
 * @author Chris Goffinet <cg@chrisgoffinet.com>
 */
class Milo_TraceContext
{
    /**
     * Trace id
     *
     * @var integer
     */
    private $trace_id = null;

    /**
     * Trace enabled
     *
     * @var boolean
     */
    private $enabled = false;

    /**
     * Trace rpc method
     *
     * @var string
     */
    private $rpc_method = null;

    /**
     * Span id
     *
     * @var long
     */
    private $span_id = 0;

    /**
     * Span parent id
     *
     * @var long
     */
    private $span_parent_id = 0;

    /**
     * List of Milo Spans
     *
     * @var array
     */
    private $spans = array();

    /**
     * Return the trace id
     *
     * @return integer
     */
    public function getTraceID()
    {
        return $this->trace_id;
    }

    /**
     * Set the trace id
     *
     * @return void
     */
    public function setTraceID($id)
    {
      $this->trace_id = $id;
    }

    /**
     * Get the next span id
     *
     * @return long
     */
    public function nextSpanID()
    {
      $this->span_id = Milo_Util::generateID();
      return $this->span_id;
    }

    /**
     * Get the current span id
     *
     * @return long
     */
    public function getSpanID()
    {
      return $this->span_id;
    }

    /**
     * Get the current span parent id
     *
     * @return long
     */
    public function getSpanParentID()
    {
      return $this->span_parent_id;
    }

    /**
     * Add a span to the list
     *
     * @return T_Base_Span
     */
    public function addSpan($span)
    {
        $this->spans[] = $span;
        return $span;
    }

    /**
     * Get the list of spans
     *
     * @return array
     */
    public function getSpans()
    {
        return $this->spans;
    }

}

?>
