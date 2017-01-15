<?php
require_once("components/blog_entry.php");
class Tile extends BlogEntry {

	private $class_list = array();

	public function __construct() {
		$args = func_get_args();
		$this->xml = count($args) == 1 ? $args[0] : $args;
		parent::__construct($args);
	}

	public function html() {
		global $blog;
		if (count($this->img) === 1) {
			$this->class_list[] = 'single-img';
		}
		$blog->javascript = "tile";
		require("html.php");
	}

	public function __get($var) {
		switch ($var) {
		case 'class_list':
			return implode(' ', $this->$var);
		}
		return parent::__get($var);
	}
}
