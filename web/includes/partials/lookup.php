<?php
    $trace_id = isset($_GET["trace_id"]) ? $_GET["trace_id"] : "";
?>
    <form action="view.php" method="GET" accept-charset="utf-8">
        <label for="trace_id">Trace ID:</label><input type="text" name="trace_id" value="<?php echo $trace_id ?>" id="trace_id" size="40">


        <p><input type="submit" value="Lookup &rarr;"></p>
    </form>