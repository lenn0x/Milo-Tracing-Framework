<?php
    $name = isset($_GET["name"]) ? $_GET["name"] : "";
    $key = isset($_GET["key"]) ? $_GET["key"] : "";
    $value = isset($_GET["value"]) ? $_GET["value"] : "";
?>
    <h3>Search</h3>
    <form action="search.php" method="get" accept-charset="utf-8">
        <div>
            <label for="name">Name:</label><input type="text" name="name" value="<?php echo $name ?>" id="name"> (e.g. Bobcat.Request)
        </div>
        <div>
            <label for="key">Key:</label><input type="text" name="key" value="<?php echo $key ?>" id="key"> (e.g. path)
        </div>
        <div>
            <label for="value">Value:</label><input type="text" name="value" value="<?php echo $value ?>" id="value"> (e.g. /top)
        </div>

        <p><input type="submit" value="Search"></p>
    </form>
