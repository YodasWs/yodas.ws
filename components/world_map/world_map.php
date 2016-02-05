<?php
chdir($_SERVER['DOCUMENT_ROOT']);
require_once("components/component.php");
class WorldMap implements Component {

	private static $self;
	private $top_places = array();
	private $xml;

	public static function html() {
		global $blog;
#		$blog->javascript = "world_map";
#		$blog->javascript = "http://maps.google.com/maps/api/js?v=3&region=US";
		require_once("components/world_map/html.php");
	}

	public static function singleton() {
		if (empty(self::$self)) {
			self::$self = new WorldMap();
		}
		return self::$self;
	}

	public static function grabLocation($loc) {
		$wm = self::$self;
		return $wm->getLocation($loc);
	}

	public function getLocation($loc) {
		foreach ($this->xml['locale'] as $l) {
			if (!empty($l['name']) and (
				(string) $l['name'] == $loc or BlogSite::urlencode((string) $l['name']) == $loc
			)) return $l;
		}
		return false;
	}

	public function __construct() {
		$this->xml = json_decode(json_encode(simplexml_load_file('world2.xml')), true);
		if (!empty($this->xml['locale']['@attributes']))
			$this->xml['locale'] = array($this->xml['locale']);
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
				if (empty($a['img']) and !empty($b['img'])) return 1;
				if (!empty($a['img']) and empty($b['img'])) return -1;
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
