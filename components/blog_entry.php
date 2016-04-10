<?php
require_once("components/component.php");
require_once("components/img/img.php");
class BlogEntry implements Component {
	private $title;
	private $img = array();
	private $url;
	private $xml;
	private $lang;

	public static function buildFromDate($date) {
		if (is_string($date)) {
			$date = BlogSite::getDate($date);
		} else throw Exception("Invalid date given");
		return new BlogEntry("{$date['year']}/{$date['mon']}/{$date['day']}");
	}

	public function __construct() {
		global $blog;
		$args = func_get_args();
		if (is_array($args[0])) $args = $args[0];
		if (is_object($args[0])) {
			return false;
		} else if (is_string($args[0]) and preg_match("'^/?\d{4}(/\d\d(/\d\d)?)?'", $arg[0])) {
			// TODO: Is Date, Load Entry(-ies)
			$date = BlogSite::getDate($arg[0]);

			$this->xml = BlogSite::getXMLFile("{$date['year']}/{$date['mon']}/{$date['day']}", $lang);
#			echo "<pre>" . print_r($this->xml, true) . "</pre>";
		} else if (is_array($args[0])) {
			// If World Map XML, take it
			if (!empty($args[0]['locale'])) {
				$this->xml = $args[0]['locale'];
			} else if (!empty($args[0]['name'])) {
				$this->xml = count($args) == 1 ? $args[0] : $args;
			// TODO: Is this a Date?
			} else {
				return false;
			}
		} else if (gettype($args[0]) == 'string') {
			// Not Date, Check world.xml
			$wm = $blog->getWorldMap();
			$loc = $wm->grabLocation($args[0]);
			if (!empty($loc)) {
				$this->xml = $loc;
			} else return false;
		}
		if (!empty($this->xml['name'])) {
			$this->title = (string) $this->xml['name'];
			if (empty($this->url)) {
				$this->url = BlogSite::urlencode($this->title);
			}
		}
	}

	public function __destruct() {
	}

	public function __get($var) {
		if (in_array($var, array(
			'xml','title',
		))) return $this->$var;
		switch ($var) {
		case 'img':
			if (!empty($this->img)) return $this->img;
			if (empty($this->xml['img'])) return array();
			if (is_string($this->xml['img'])) {
				$this->xml['img'] = array($this->xml['img']);
			}
			foreach ($this->xml['img'] as $i) {
				$this->img[] = new Img((string) $i);
			}
			return $this->img;
		}
	}
	public function __set($var, $val) {
		if (in_array($var, array(
			'title',
		)) and is_string($val)) $this->$var = $val;
	}
	public function __isset($var) {
		if (in_array($var, array(
			'img'
		))) return count($this->xml['img']) > 0;
	}

}
