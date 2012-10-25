<?php
if(isset($_SESSION['message'])) {
	echo "<div class=\"message\">{$_SESSION['message']}</div>";
	unset($_SESSION['message']);
}

$file = "views/$CURR_PAGE.php";
if(file_exists($file)) {
	include($file);
} else {
	include('views/404.php');
}