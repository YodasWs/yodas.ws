<?php
require_once("components/component.php");
require_once("components/img/img.php");
class BlogEntry implements Component {
	private $title;
	private $img = array();
	private $date;
	private $url;
	private $xml;

	public static function buildFromDate($date) {
		if (is_string($date)) {
			$this->date = BlogSite::getDate($date);
		} else throw Exception("Invalid date given");
		return new BlogEntry("{$this->date['year']}/{$this->date['mon']}/{$this->date['day']}");
	}

	public function __construct() {
		global $blog;
		$args = func_get_args();
		if (is_array($args[0])) $args = $args[0];
		if (is_object($args[0])) {
			return false;
		} else if (is_string($args[0]) and preg_match("'^/?\d{4}(/(\d\d|[JFMASOND][aepuco][nbrylgptvc])(/\d\d)?)?/?'", $args[0])) {
			// TODO: Is Date, Load Entry(-ies)
			$this->date = BlogSite::getDate($args[0]);

			$this->xml = $blog->getXMLFile(
				$this->date['year'] . '/' . BlogSite::str_num($this->date['mon']) . '/' . $this->date['day']
			);
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
			$loc = $wm->getLocation($args[0]);
			if (!empty($loc)) {
				$this->xml = $loc;
			} else return false;
		}
	}

	public function __destruct() {
	}

	public function __get($var) {
		if (in_array($var, array(
			'xml',
		))) return $this->$var;
		switch ($var) {
		case 'url':
			if (!empty($this->url)) return $this->url;
			if (empty($this->title)) $this->__get('title');

			if (!empty($this->date)) {
				$this->url = BlogSite::date_as_url($this->date);
			} else {
				$loc = WorldMap::grabLocation($this->title);
				if (!empty($loc)) {
					if (!empty($loc['date']) and (count($loc['date']) === 1 or is_string($loc['date']))) {
						$this->url = BlogSite::urlencode($loc['date']);
					} else {
						$this->url = '/' . $loc['@attributes']['cc'] . BlogSite::urlencode($loc['name']);
					}
				} else {
					$cc = WorldMap::getCountryCode($this->title);
					if (!empty($cc)) {
						$this->url = "/{$cc}/";
					}
				}
			}

			if (empty($this->url)) {
				$this->url = BlogSite::urlencode($this->title);
			}

			return $this->url;
		case 'title':
			if (!empty($this->title)) return $this->title;
			if (!empty($this->xml['name'])) {
				$this->title = (string) $this->xml['name'];
			} else if (!empty($this->date)) {
				$this->title = BlogSite::date_toString($this->date);
			}
			return $this->title;
		case 'img':
			if (!empty($this->img)) return $this->img;
			if (empty($this->xml['img'])) return array();
			if (!is_array($this->xml['img'])) {
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
		))) return !empty($this->xml['img']) && count($this->xml['img']) > 0;
	}

}
