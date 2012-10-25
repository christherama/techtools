<?php
$tools = Tool::getAll();

foreach($tools as $tool) {
	echo $tool;
}