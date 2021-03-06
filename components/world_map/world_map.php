<?php
chdir("{$_SERVER['DOCUMENT_ROOT']}/{$_SERVER['SITE_DIR']}");
require_once("components/component.php");
class WorldMap implements Component {

	private static $self;
	private $top_places = array();
	private $xml;

	public static function html() {
		global $blog;
		$blog->javascript = "world_map";
		$blog->javascript = "google-maps";
		require_once("components/world_map/html.php");
	}

	public static function singleton() {
		if (empty(self::$self)) {
			self::$self = new WorldMap();
		}
		return self::$self;
	}

	public static function grabLocation($loc) {
		$wm = self::singleton();
		return $wm->getLocation($loc);
	}

	public function getLocation($loc) {
		foreach ($this->xml['locale'] as $l) {
			if (!empty($l['name']) and (
				(string) $l['name'] == $loc or BlogSite::urlencode((string) $l['name']) == $loc
			)) {
				if (empty($l['img'])) $l['img'] = array();
				if (is_string($l['img'])) $l['img'] = array($l['img']);
				return $l;
			}
		}
		return false;
	}

	public static function grabImages($location) {
		$wm = self::singleton();
		return $wm->getImages($loc);
	}
	public function getImages($location) {
		$xml = $this->getLocation($location);
		if (empty($xml)) return array();
		if (!is_array($xml['img'])) $xml['img'] = array($xml['img']);
		$img = array();
		require_once("components/img/img.php");
		if (!empty($xml['img'])) foreach ($xml['img'] as $i) {
			$img[] = new Img($i);
		}
		return $img;
	}

	public function locationsByCountry() {
		$list = array();
		foreach ($this->xml['locale'] as $l) {
			$list[$l['@attributes']['cc']][] = $l;
		}
		uasort($list, function($a, $b) {
			if (count($a) == count($b)) return 0;
			return (count($a) < count($b)) ? 1 : -1;
		});
		foreach ($list as $cc => &$l) {
			$l['name'] = $this->getCountryName($cc);
			$l['cc'] = $cc;
		}
		return $list;
	}

	public function getByCountry($cc) {
		$countries = $this->locationsByCountry();
		if (!empty($countries[$cc])) return $countries[$cc];
		return array();
	}

	private function getLocalWorldMap($lang = null) {
		return BlogSite::getXMLFile('world', $lang);
	}

	public function getCountryName($cc, $lang=null) {
		global $blog;
		$xml = $this->getLocalWorldMap($lang);
		if (!empty($xml)) foreach ($xml['country'] as $c) {
			if ($c['@attributes']['cc'] == $cc) return $c['name'];
		}
		return false;
	}

	public static function getCountryCode($name, $lang=null) {
		global $blog;
		$wm = self::singleton();
		$xml = $wm->getLocalWorldMap($lang);
		if (!empty($xml)) foreach ($xml['country'] as $c) {
			if (
				(!empty($c['name']) and $c['name'] == $name) or
				(!empty($c['long']) and $c['long'] == $name)
			) return $c['@attributes']['cc'];
		}
		return false;
	}

	public function __construct() {
		global $blog;
		$this->xml = json_decode(json_encode(simplexml_load_file("{$_SERVER['DOCUMENT_ROOT']}/{$_SERVER['SITE_DIR']}/world2.xml")), true);
		if (!empty($this->xml['locale']['@attributes']))
			$this->xml['locale'] = array($this->xml['locale']);
		$lang_xml = $this->getLocalWorldMap($blog->lang);
		if (is_array($lang_xml)) {
			$this->xml = array_merge($this->xml, $lang_xml);
		}
	}

	public function __get($var) {
		if (in_array($var, array(
		))) return $this->$var;
		switch ($var) {
		case 'top_places':
			if (!empty($this->top_places)) return $this->top_places;
			$this->top_places = array();
			foreach ($this->xml['locale'] as $l) {
				$this->top_places[] = $l;
			}
			uasort($this->top_places, function($a, $b) {
				if (empty($a['img']) && empty($b['img'])) return 0;
				if (empty($a['img']) and !empty($b['img'])) return 1;
				if (!empty($a['img']) and empty($b['img'])) return -1;
				if (is_string($a['img'])) $a['img'] = array($a['img']);
				if (is_string($b['img'])) $b['img'] = array($b['img']);
				if (!empty($a['img']) and !empty($b['img'])) {
					if (count($a['img']) == count($b['img'])) return 0;
					return (count($a['img']) < count($b['img'])) ? 1 : -1;
				}
				return 0;
			});
			return $this->top_places;
		}
	}

}
