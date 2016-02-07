<?php
require_once("components/blog_entry.php");
class Tile extends BlogEntry {

	public function __construct() {
		$args = func_get_args();
		$this->xml = count($args) == 1 ? $args[0] : $args;
		parent::__construct($args);
	}

	public function html() {
		global $blog;
		$blog->javascript = "tile";
		require("html.php");
	}
}
