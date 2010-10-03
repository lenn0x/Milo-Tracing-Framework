    <h3>Saved Searches</h3>
    <?php
        $results = mysql_query("SELECT * FROM saved_searches");
    ?>
    <?php if(mysql_num_rows($results) == 0): ?>
        No saved searches.
    <?php else: ?>
        <table border="0" cellspacing="1" cellpadding="5" width="100%">
            <tr>
                <th width="50%" align="left">Name</th>
                <th align="left">Search Query</th>
            </tr>
            <?php while($row = mysql_fetch_assoc($results)) { ?>
                <tr>
                    <td><a href="search.php?<?php echo "name=" . urlencode($row["name"]) . "&key=" . urlencode($row["key"]) . "&value=" . urlencode($row["value"]) ?>"><?php echo $row["search_name"] ?></a></td>
                    <td>Name: <?php echo $row["name"] ?>, Key: <?php echo !empty($row["key"]) ? $row["key"] : "{empty}" ?>, Value: <?php echo !empty($row["value"]) ? $row["value"] : "{empty}" ?></td>
                </tr>
            <?php } ?>

        </table>
    <?php endif ?>
