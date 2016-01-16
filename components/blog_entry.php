<?php
require_once("components/component.php");
class BlogEntry implements Component {
	private $title;

	public static function buildFromDate($date) {
		if (is_string($date)) {
			$date = BlogSite::getDate($date);
		} else throw Exception("Invalid date given");
		return new BlogEntry("{$date['year']}/{$date['mon']}/{$date['day']}");
	}

	public function __construct() {
		global $blog;
		$args = func_get_args();
		if (gettype($args[0]) == 'array') {
			$args = $args[0];
		}
		if (gettype($args[0]) == 'object') {
			switch (get_class($args[0])) {
			case 'SimpleXMLElement':
				$this->title = (string) $args[0]->google;
				break;
			}
		} else if (gettype($args[0]) == 'string' and preg_match("'^/\d{4}(/\d\d(/\d\d)?)?'", $arg[0])) {
			// TODO: Is Date, Load Entry(-ies)
			$date = BlogSite::getDate($arg[0]);
		} else if (gettype($args[0]) == 'array') {
			// TODO: Is this a Date?
		} else if (gettype($args[0]) == 'string') {
			// TODO: Not Date, Check world.xml
			$wm = $blog->getWorldMap();
			$this->title = $args[0];
		}
	}

	public function __destruct() {
	}

	public function __get($var) {
		if (in_array($var, array(
			'title',
		))) return $this->$var;
	}
	public function __set($var, $val) {
		if (in_array($var, array(
			'title',
		)) and is_string($val)) $this->$var = $val;
	}

}
