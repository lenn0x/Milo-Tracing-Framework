<?php
 include "./includes/client.php";
 include "./includes/mysql.php";

 $client = get_client();

?>
<?php include "includes/header.php" ?>
    <?php include "./includes/partials/lookup.php" ?>
    <?php include "./includes/partials/search.php" ?>
    <?php include "./includes/partials/saved_searches.php" ?>
<?php include "includes/footer.php" ?>
