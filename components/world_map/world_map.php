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

	public function __construct() {
		$this->xml = simplexml_load_file('world.xml');
		$this->top_places = array();
		foreach ($this->xml->locale as $l) {
			$this->top_places[(string)$l->google] = count($l->img);
		}
		arsort($this->top_places);
		$this->top_places = array_keys($this->top_places);
	}

	public function __get($var) {
		if (in_array($var, array(
			'top_places',
		))) return $this->$var;
	}

}
