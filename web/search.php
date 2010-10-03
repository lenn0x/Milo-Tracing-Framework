<?php
 include "./includes/client.php";
 include "./includes/mysql.php";

 $client = get_client();
 $page_title = "Search";
 $name = $_GET["name"];
 $key = $_GET["key"];
 $value = $_GET["value"];
 $fields = array();

 $schema_mapper = array("name" => "name", "key" => "annotation_name", "value" => "annotation_value");

 foreach (array("name", "key", "value") as $key) {
    if(!empty($_GET[$key])) {
        $value = $_GET[$key];
        $mapped = $schema_mapper[$key];
        $fields[] = "`$mapped` LIKE '" . mysql_real_escape_string($value) . "'";
    }
 }
 $max = 25;
 $start = isset($_GET["start"]) ? $_GET["start"] : 0;
 $offset = isset($_GET["offset"]) ? $_GET["offset"] : $max;

 if (!empty($fields)) {
    $fields = "WHERE " . implode(" AND ", $fields);
 }
 $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM annotations $fields ORDER BY date_created DESC LIMIT $start, $offset";
 $results = mysql_query($sql);
?>
<?php include "includes/header.php" ?>
    <?php include "./includes/partials/search.php" ?>
    <hr>
    <?php if (mysql_num_rows($results) == 0): ?>
        No matches could be found for your search.
    <?php else: ?>
        <ol>
        <?php while($row = mysql_fetch_array($results)) { ?>
                <li><a href="view.php?trace_id=<?php echo $row["trace_id"] ?>"><?php echo $row["trace_id"] ?></a>
                <ul>
                    <li><strong>name:</strong> <?php echo $row["name"] ?></li>
                    <li><strong>key:</strong> <?php echo $row["annotation_name"] ?></li>
                    <li><strong>value:</strong> <?php echo $row["annotation_value"] ?></li>
                    <li><strong>date_created:</strong> <?php echo $row["date_created"] ?></li>
                </ul>
            <?php } ?>
        </ol>
    <?php endif ?>
    <?php
        $query = mysql_query("SELECT FOUND_ROWS()");
        $rows = mysql_fetch_row($query);
        $rows = $rows[0];
    ?>
    <?php if ($start-1 >= 0): ?>
        <a href="search.php?name=<?php echo $name ?>&key=<?php echo $key ?>&value=<?php echo $value ?>&start=<?php echo $start - 1 ?>">Previous</a>
    <?php endif ?>
    <?php if ($start+$offset < $rows): ?>
        <a href="search.php?name=<?php echo $name ?>&key=<?php echo $key ?>&value=<?php echo $value ?>&start=<?php echo $start + 1 ?>">Next</a>
    <?php endif ?>
    <br/>
    <br/>
<?php include "includes/footer.php" ?>
