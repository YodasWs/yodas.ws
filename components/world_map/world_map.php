<?php
require_once("components/component.php");
class WorldMap implements Component {

	private static $self;
	private $top_places;
	private $xml;

	public static function html() {
		global $blog;
		$blog->javascript = "world_map";
		require_once("components/world_map/html.php");
	}

	public static function singleton() {
		if (empty(self::$self)) {
			self::$self = new WorldMap();
		}
		return self::$self;
	}

	public function getLocation($loc) {
		foreach ($this->xml->locale as $l) {
			if (!empty($l->google) and (string) $l->google == $loc) return $l;
		}
		return false;
	}

	public function __construct() {
		$this->xml = simplexml_load_file('world.xml');
		$this->top_places = array();
		foreach ($this->xml->locale as $l) {
			$this->top_places[] = $l;
		}
		uasort($this->top_places, function($a, $b) {
			if (empty($a->img) and !empty($b->img)) return 1;
			if (!empty($a->img) and empty($b->img)) return -1;
			if (!empty($a->img) and !empty($b->img)) {
				if (count($a->img) == count($b->img)) return 0;
				return (count($a->img) < count($b->img)) ? 1 : -1;
			}
			return 0;
		});
	}

	public function __get($var) {
		if (in_array($var, array(
			'top_places',
		))) return $this->$var;
	}

}
