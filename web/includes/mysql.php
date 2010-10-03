<?php
    include_once "config.php";

    mysql_connect($GLOBALS['CONFIG']['MYSQL']['HOST'], $GLOBALS['CONFIG']['MYSQL']['USER'], $GLOBALS['CONFIG']['MYSQL']['PASS']);
    mysql_select_db($GLOBALS['CONFIG']['MYSQL']['DB']);
?>