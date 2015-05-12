<?php
include( dirname(__FILE__)."/include/initializer.php");
$site->registrate($_GET['key'], $_GET['is_driver']);
?>