<?php
class Debug {
	public static function i($name,$what) {
		echo "<pre><strong style=\"font-size:14pt;background-color:#fff;display:block;padding:6px;\">$name</strong><br/>";
		var_dump($what);
		echo '</pre>';
	}
}