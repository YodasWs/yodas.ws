<?php
require_once("components/component.php");
class BlogEntry implements Component {
	private $title;
	private $img = array();
	private $url;
	private $xml;

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
				$this->xml = $args[0];
				break;
			default:
				return false;
			}
		} else if (gettype($args[0]) == 'string' and preg_match("'^/\d{4}(/\d\d(/\d\d)?)?'", $arg[0])) {
			// TODO: Is Date, Load Entry(-ies)
			$date = BlogSite::getDate($arg[0]);
			return false;
		} else if (gettype($args[0]) == 'array') {
			// TODO: Is this a Date?
			return false;
		} else if (gettype($args[0]) == 'string') {
			// Not Date, Check world.xml
			$wm = $blog->getWorldMap();
			$loc = $wm->grabLocation($args[0]);
			if (!empty($loc)) {
				$this->xml = $loc;
			} else return false;
		}
		if (!empty($this->xml->google)) {
			$this->title = (string) $this->xml->google;
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
			if (empty($this->xml->img)) return array();
			if (!empty($this->img)) return $this->img;
			foreach ($this->xml->img as $i) {
				if (strpos($i['src'], 'http') === false) {
					$i['src'] = "http://yodas.ws/{$i['src']}";
				}
				$this->img[] = $i;
			}
			shuffle($this->img);
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
		))) return count($this->xml->img) > 0;
	}

}
