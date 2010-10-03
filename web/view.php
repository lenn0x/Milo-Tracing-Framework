<?php
 include "./includes/client.php";
 include "./includes/mysql.php";
 include "./thrift/util.php";
 include "./includes/milo_types.php";

 function get_duration($span, $span_type) {
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
         } else if ($span_type == "client") {
             if ($event->event_type == milo_EventType::CLIENT_SEND) {
                 $start_time = $event->timestamp;
             } else if ($event->event_type == milo_EventType::CLIENT_RECV) {
                 $end_time = $event->timestamp;
             }
             if (isset($start_time) && isset($end_time)) {
                 break;
             }
         }
       }
       $duration = $end_time - $start_time;
     }
     return $duration;
 }

 $client = get_client();
 $client->transport->open();
 $page_title = "View Trace";
 $trace_id = $_GET["trace_id"];
 $span_id = isset($_GET["span_id"]) ? (integer)$_GET["span_id"] : null;
 $parent_id = isset($_GET["parent_id"]) ? (integer)$_GET["parent_id"] : 0;
?>
<?php include "includes/header.php" ?>
    <?php include "./includes/partials/lookup.php" ?>

    <?php
      $column_parent = new cassandra_ColumnParent(array("column_family" => "Traces"));
      $slice_range = new cassandra_SliceRange(array("start" => "", "finish" => "", "count" => 10000));
      $predicate = new cassandra_SlicePredicate(array("slice_range" => $slice_range));

      $spans = array();
      $rootNode = null;
      $columns = $client->get_slice("Milo", $trace_id, $column_parent, $predicate, cassandra_ConsistencyLevel::ONE);

      foreach($columns as $column) {
        $column = $column->column;
        list($sid, $spid, $span_type) = explode("-", $column->name);
        $span = thrift_unserialize($column->value, new milo_Span());

        $key = "$sid-$spid";
        $spans[$key] = array($span_type, $span);
        if ($spid == $parent_id ) {
            $rootNode = $span;
        }
     }
     $tree = array();
     $total_duration = 0;

     foreach ($spans as $key => $value) {
        list($span_type, $span) = $value;
        if ($span->parent_id == $rootNode->id ) {
            $tree[$key] = $span;

            $duration = get_duration($span, $span_type);
            $total_duration += $duration;
        }
     }
    ?>
    <h3><?php echo $rootNode->name ?> (<?php echo $total_duration ?> ms)</h3>
    <p>
            (<strong><?php echo count($tree) ?> sub calls, <?php echo count($spans)-1 ?> total calls</strong>)

        <a href="callgraph.php?trace_id=<?php echo $trace_id ?>">View Callgraph</a>
    </p>
    <p>
        <table border="1" cellspacing="1" cellpadding="5">
        <tr>
            <th>Name</th>
            <th>Value</th>
        </tr>
        <?php $keys = array("trace_id", "name", "id", "parent_id", "client_host", "server_host", ) ?>
        <?php foreach($keys as $key): ?>
        <tr>
            <td><?php echo $key ?></td>
            <td><?php echo $rootNode->$key ?></td>
        </tr>
        <?php endforeach ?>
        </table>
    </p>
    <?php if (count($tree)): ?>
        <h4>Calls</h4>

        <table border="1" cellspacing="1" cellpadding="5" width="100%">
            <tr>
                <th>Call</th>
                <th>Time (microseconds)</th>
            </tr>
            <?php foreach ($tree as $key => $span): ?>
            <?php
                $call = $span->name;
                $duration = get_duration($span, "server");

                if ($duration > 50000) {
                    $color = "high";
                } else if($duration> 25000) {
                    $color = "medium";
                } else {
                    $color = "low";
                }
            ?>
                <tr>
                    <td class="<?php echo $color ?>"><a href="view.php?trace_id=<?php echo $trace_id ?>&span_id=<?php echo $span->id ?>&parent_id=<?php echo $span->parent_id ?>"><?php echo $call ?></a></td>
                    <td class="<?php echo $color ?>"><?php echo number_format($duration) ?></td>
                </tr>
            <?php endforeach ?>
        </table>
    <?php endif ?>
<?php include "includes/footer.php" ?>
