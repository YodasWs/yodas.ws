<?php
require_once("components/blog_entry.php");
class Tile extends BlogEntry {

	public function __construct() {
		$args = func_get_args();
		parent::__construct($args);
	}

	public function html() {
		require("html.php");
	}
}
