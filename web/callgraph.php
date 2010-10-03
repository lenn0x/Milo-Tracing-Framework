<?php
  include "./includes/client.php";
  include "./includes/milo_types.php";
  include "./thrift/util.php";
  require_once './graphing/GraphViz.php';

  function get_color($duration) {
      if($duration > 50000) {
          $color = "red";
      } else if($duration > 20000) {
          $color = "yellow";
      } else {
          $color = "white";
      }
      return $color;
  }

  $trace_id = $_GET['trace_id'];
  $client = get_client();

  $client->transport->open();
  $column_parent = new cassandra_ColumnParent(array("column_family" => "Traces"));
  $slice_range = new cassandra_SliceRange(array("start" => "", "finish" => "", "count" => 10000));
  $predicate = new cassandra_SlicePredicate(array("slice_range" => $slice_range));

  $spans = array();
  $columns = $client->get_slice("Milo", $trace_id, $column_parent, $predicate, cassandra_ConsistencyLevel::ONE);
  $client->transport->close();
  $graph = new Image_GraphViz();
  $total_duration = 0;

  foreach ($columns as $column) {
      $column = $column->column;
      list($span_id, $span_parent_id, $span_type) = explode("-", $column->name);
      $span = thrift_unserialize($column->value, new milo_Span());
      if ($span_parent_id == 0) {
        $rootNode = $span;
      }

      $start_time = null;
      $end_time = null;
      $duration = 0;

      if ($span->events) {
          foreach ($span->events as $event) {
            if ($span_type == "server") {
                if ($event->event_type == milo_EventType::SERVER_SEND) {
                    $end_time = $event->timestamp;
                } else if ($event->event_type == milo_EventType::SERVER_RECV) {
                    $start_time = $event->timestamp;
                }
                if (isset($start_time) && isset($end_time)) {
                    break;
                }
            }
          }
          $duration = $end_time - $start_time;
          $total_duration += $duration;
      }

     if ($span_parent_id != 0) {
          $graph->addNode(
          $span_id,
          array(
           'label' => "$span->name ($duration ms)",
           'shape' => 'box',
           'fillcolor' => get_color($duration),
           'style' => 'filled'
          )
         );

         $graph->addEdge(array($span_parent_id => $span_id));
     }

  }
   $graph->addNode(
      $rootNode->id,
      array(
       'label' => "$rootNode->name ($total_duration ms)",
       'shape' => 'box',
       'fillcolor' => get_color($total_duration),
       'style' => 'filled'
      )
   );

  $graph->image("png");
?>
